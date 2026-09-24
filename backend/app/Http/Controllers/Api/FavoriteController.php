<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Favorite;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()->with('event')->get();

        return response()->json($favorites->map(fn($f) => $f->event));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => ['required', 'string', 'regex:/^[a-zA-Z0-9|-]+$/'],
        ]);

        $eventId = $request->event_id;

        // Vérifie si l'évènement est déjà en favori (via l'id interne de la table events)
        $existing = Event::where('ticketmaster_id', $eventId)->first();

        if ($existing && Favorite::where('user_id', $request->user()->id)->where('event_id', $existing->id)->exists()) {
            return response()->json(['message' => 'Déjà en favori'], 409);
        }

        if (str_starts_with($eventId, 'user-')) {
            // Évènement créé par un utilisateur : lu en base
            $userEvent = UserEvent::find((int) substr($eventId, 5));

            if (!$userEvent) {
                return response()->json(['error' => 'Évènement introuvable'], 404);
            }

            $attributes = [
                'title' => $userEvent->title,
                'date' => $userEvent->date->format('Y-m-d'),
                'city' => $userEvent->city,
                'latitude' => $userEvent->latitude,
                'longitude' => $userEvent->longitude,
                'image_url' => $userEvent->image ? asset('storage/' . $userEvent->image) : null,
                'ticket_url' => null,
                'source' => 'user',
            ];
        } else {
            // Évènement Ticketmaster
            [$attractionId, $venueId] = array_pad(explode('|', $eventId, 2), 2, null);

            $apiKey = config('services.ticketmaster.key');
            $httpClient = app()->environment('local') ? Http::withoutVerifying() : Http::withOptions([]);

            // Tente d'abord comme id d'attraction (event groupé)
            $response = $httpClient->get('https://app.ticketmaster.com/discovery/v2/events.json', [
                'apikey' => $apiKey,
                'attractionId' => $attractionId,
                'size' => 100,
            ]);

            $events = collect($response->json('_embedded.events') ?? []);

            if ($venueId && $venueId !== 'no-venue') {
                $events = $events->filter(function ($e) use ($venueId) {
                    return ($e['_embedded']['venues'][0]['id'] ?? null) === $venueId;
                })->values();
            }

            // Fallback : id d'event direct (pas une attraction)
            if ($events->isEmpty()) {
                $single = $httpClient->get("https://app.ticketmaster.com/discovery/v2/events/{$attractionId}.json", [
                    'apikey' => $apiKey,
                ]);

                if ($single->failed()) {
                    return response()->json(['error' => 'Évènement introuvable'], 404);
                }

                $events = collect([$single->json()]);
            }

            $data = $events->first();

            $attributes = [
                'title' => $data['name'],
                'date' => $data['dates']['start']['localDate'] ?? null,
                'city' => $data['_embedded']['venues'][0]['city']['name'] ?? null,
                'latitude' => $data['_embedded']['venues'][0]['location']['latitude'] ?? null,
                'longitude' => $data['_embedded']['venues'][0]['location']['longitude'] ?? null,
                'image_url' => $data['images'][0]['url'] ?? null,
                'ticket_url' => $data['url'] ?? null,
                'source' => 'ticketmaster',
            ];
        }

        $event = Event::updateOrCreate(
            ['ticketmaster_id' => $eventId],
            $attributes
        );

        Favorite::create([
            'user_id' => $request->user()->id,
            'event_id' => $event->id,
        ]);

        return response()->json(['message' => 'Ajouté aux favoris'], 201);
    }

    public function destroy(Request $request, string $eventId)
    {
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