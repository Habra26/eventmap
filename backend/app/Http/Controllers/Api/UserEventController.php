<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserEventController extends Controller
{
    private const CATEGORIES = ['Music', 'Sports', 'Arts & Theatre', 'Family', 'Film', 'Miscellaneous'];

    private function httpClient()
    {
        return app()->environment('local') ? Http::withoutVerifying() : Http::withOptions([]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'nullable|date_format:H:i',
            'venue' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'category' => ['nullable', Rule::in(self::CATEGORIES)],
            'image' => 'nullable|image|max:5120',
        ], [
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
        ]);

        [$latitude, $longitude] = $this->geocode($data['address'], $data['city']);

        if ($latitude === null) {
            throw ValidationException::withMessages([
                'address' => 'Adresse introuvable. Vérifie l\'adresse et la ville.',
            ]);
        }

        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('user-events', 'public');
        }

        $event = $request->user()->userEvents()->create($data);

        return response()->json($event, 201);
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