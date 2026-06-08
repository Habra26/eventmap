<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// Gère les favoris de l'utilisateur connecté
// Les événements Ticketmaster sont sauvegardés en DB au moment de l'ajout en favori
class FavoriteController extends Controller
{
    // Retourne la liste des événements mis en favori par l'utilisateur
    public function index(Request $request)
    {
        // eager load 'event' pour éviter N+1 requêtes SQL (une seule requête au lieu d'une par favori)
        $favorites = $request->user()->favorites()->with('event')->get();

        // On aplatit pour n'exposer que les données de l'événement, pas la table pivot
        return response()->json($favorites->map(fn($f) => $f->event));
    }

    public function store(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);

        $eventId = $request->event_id;

        // Vérifie d'abord si le favori existe déjà pour renvoyer 409 plutôt qu'une erreur SQL
        $already = Favorite::where('user_id', $request->user()->id)
            ->where('event_id', $eventId)
            ->exists();

        if ($already) {
            return response()->json(['message' => 'Déjà en favori'], 409);
        }

        // Récupère l'évènement depuis Ticketmaster et le sauvegarde en DB
        $apiKey = config('services.ticketmaster.key');
        $response = (app()->environment('local') ? Http::withoutVerifying() : Http::withOptions([]))
            ->get("https://app.ticketmaster.com/discovery/v2/events/{$eventId}.json", [
                'apikey' => $apiKey,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Évènement introuvable'], 404);
        }

        $data = $response->json();

        // updateOrCreate évite les doublons si l'événement a déjà été mis en favori par un autre utilisateur
        // La clé de recherche est le ticketmaster_id (identifiant externe stable)
        $event = Event::updateOrCreate(
            ['ticketmaster_id' => $eventId],
            [
                'title' => $data['name'],
                'date' => $data['dates']['start']['localDate'] ?? null,
                'city' => $data['_embedded']['venues'][0]['city']['name'] ?? null,
                'latitude' => $data['_embedded']['venues'][0]['location']['latitude'] ?? null,
                'longitude' => $data['_embedded']['venues'][0]['location']['longitude'] ?? null,
                'image_url' => $data['images'][0]['url'] ?? null,
                'ticket_url' => $data['url'] ?? null,
                'source' => 'ticketmaster',
            ]
        );

        Favorite::create([
            'user_id' => $request->user()->id,
            'event_id' => $event->id, // id interne DB, pas le ticketmaster_id
        ]);

        return response()->json(['message' => 'Ajouté aux favoris'], 201);
    }

    public function destroy(Request $request, string $eventId)
    {
        // On recherche par ticketmaster_id car le frontend n'a pas accès à l'id interne DB
        $event = Event::where('ticketmaster_id', $eventId)->first();

        if (!$event) {
            return response()->json(['error' => 'Évènement introuvable'], 404);
        }

        $deleted = Favorite::where('user_id', $request->user()->id)
            ->where('event_id', $event->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Favori introuvable'], 404);
        }

        return response()->json(['message' => 'Retiré des favoris']);
    }
}
