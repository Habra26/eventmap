<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Favorite;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserEventController extends Controller
{
    private const CATEGORIES = ['Music', 'Sports', 'Arts & Theatre', 'Family', 'Film', 'Miscellaneous'];

    private function httpClient()
    {
        return app()->environment('local') ? Http::withoutVerifying() : Http::withOptions([]);
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'nullable|date_format:H:i',
            'venue' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'category' => ['nullable', Rule::in(self::CATEGORIES)],
            'image' => 'nullable|image|max:5120',
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères.',
            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date n\'est pas valide.',
            'date.after_or_equal' => 'La date ne peut pas être dans le passé.',
            'time.date_format' => 'L\'heure doit être au format HH:MM.',
            'venue.max' => 'Le nom du lieu ne peut pas dépasser 255 caractères.',
            'address.required' => 'L\'adresse est obligatoire.',
            'address.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'city.required' => 'La ville est obligatoire.',
            'city.max' => 'La ville ne peut pas dépasser 100 caractères.',
            'category.in' => 'La catégorie choisie n\'est pas valide.',
            'image.image' => 'Le fichier doit être une image (jpg, png, gif, webp…).',
            'image.max' => 'L\'image ne peut pas dépasser 5 Mo.',
            'image.uploaded' => 'L\'image n\'a pas pu être envoyée. Elle est peut-être trop lourde.',
        ];
    }

    public function index(Request $request)
    {
        $events = $request->user()->userEvents()
            ->orderBy('date')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => 'user-' . $event->id,
                    'title' => $event->title,
                    'date' => $event->date->format('Y-m-d'),
                    'time' => $event->time ? substr($event->time, 0, 5) : null,
                    'city' => $event->city,
                    'category' => $event->category,
                    'image_url' => $event->image ? asset('storage/' . $event->image) : null,
                    'is_past' => $event->date->lt(today()),
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        [$latitude, $longitude] = $this->geocodeOrFail($data['address'], $data['city']);
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('user-events', 'public');
        }

        $event = $request->user()->userEvents()->create($data);

        return response()->json($event, 201);
    }

    public function update(Request $request, UserEvent $userEvent)
    {
        if ((int) $userEvent->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tu ne peux pas modifier cet évènement.'], 403);
        }

        $data = $request->validate(
            $this->rules() + ['remove_image' => 'nullable|boolean'],
            $this->messages()
        );

        $removeImage = $request->boolean('remove_image');
        unset($data['remove_image']);

        // Nouveau géocodage uniquement si l'adresse ou la ville a changé
        if ($data['address'] !== $userEvent->address || $data['city'] !== $userEvent->city) {
            [$latitude, $longitude] = $this->geocodeOrFail($data['address'], $data['city']);
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
        }

        // Image : remplacement, suppression, ou conservation de l'actuelle
        if ($request->hasFile('image')) {
            $this->deleteImage($userEvent);
            $data['image'] = $request->file('image')->store('user-events', 'public');
        } elseif ($removeImage) {
            $this->deleteImage($userEvent);
            $data['image'] = null;
        }

        $userEvent->update($data);

        // Met à jour la copie utilisée par les favoris, si elle existe
        Event::where('ticketmaster_id', 'user-' . $userEvent->id)->update([
            'title' => $userEvent->title,
            'date' => $userEvent->date->format('Y-m-d'),
            'city' => $userEvent->city,
            'latitude' => $userEvent->latitude,
            'longitude' => $userEvent->longitude,
            'image_url' => $userEvent->image ? asset('storage/' . $userEvent->image) : null,
        ]);

        return response()->json($userEvent->fresh());
    }

    public function destroy(Request $request, UserEvent $userEvent)
    {
        if ((int) $userEvent->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tu ne peux pas supprimer cet évènement.'], 403);
        }

        // Supprime la copie utilisée par les favoris, et les favoris qui la référencent
        $snapshot = Event::where('ticketmaster_id', 'user-' . $userEvent->id)->first();
        if ($snapshot) {
            Favorite::where('event_id', $snapshot->id)->delete();
            $snapshot->delete();
        }

        $this->deleteImage($userEvent);
        $userEvent->delete();

        return response()->json(['message' => 'Évènement supprimé']);
    }

    private function deleteImage(UserEvent $userEvent): void
    {
        if ($userEvent->image) {
            Storage::disk('public')->delete($userEvent->image);
        }
    }

    private function geocodeOrFail(string $address, string $city): array
    {
        [$latitude, $longitude] = $this->geocode($address, $city);

        if ($latitude === null) {
            throw ValidationException::withMessages([
                'address' => 'Adresse introuvable. Vérifie l\'adresse et la ville.',
            ]);
        }

        return [$latitude, $longitude];
    }

    private function geocode(string $address, string $city): array
    {
        $response = $this->httpClient()
            ->timeout(5)
            ->withHeaders(['User-Agent' => 'EventMap/1.0'])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address . ', ' . $city,
                'format' => 'json',
                'limit' => 1,
            ]);

        if ($response->ok() && count($response->json()) > 0) {
            $result = $response->json()[0];
            return [(float) $result['lat'], (float) $result['lon']];
        }

        return [null, null];
    }
}