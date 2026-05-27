<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $apiKey = config('services.ticketmaster.key');

        $params = [
            'apikey' => $apiKey,
            'size' => 20,
        ];

        if ($request->has('keyword')) {
            $params['keyword'] = $request->keyword;
        }

        if ($request->has('city')) {
            $params['city'] = $request->city;
        }

        if ($request->has('bbox')) {
            [$south, $west, $north, $east] = explode(',', $request->bbox);
            $centerLat = ($south + $north) / 2;
            $centerLng = ($west + $east) / 2;
            $params['geoPoint'] = round($centerLat, 6) . ',' . round($centerLng, 6);
            $params['radius'] = 50;
            $params['unit'] = 'km';
        } elseif (!$request->has('keyword') && !$request->has('city')) {
            $params['countryCode'] = 'BE';
        }

        $response = (app()->environment('local') ? Http::withoutVerifying() : Http::new())->get('https://app.ticketmaster.com/discovery/v2/events.json', $params);

        if ($response->failed()) {
            return response()->json(['error' => 'API Ticketmaster indisponible'], 503);
        }

        $events = collect($response->json('_embedded.events') ?? [])
            ->map(function ($event) {
                $venue = $event['_embedded']['venues'][0] ?? [];
                [$lat, $lng] = $this->getCoordinates($venue);

                return [
                    'id' => $event['id'],
                    'title' => $event['name'],
                    'date' => $event['dates']['start']['localDate'] ?? null,
                    'city' => $venue['city']['name'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'image_url' => $event['images'][0]['url'] ?? null,
                    'ticket_url' => $event['url'] ?? null,
                    'category' => $event['classifications'][0]['segment']['name'] ?? null,
                ];
            })
            ->sortBy('date')
            ->values();

        return response()->json($events);
    }

    public function show(string $id)
    {
        $apiKey = config('services.ticketmaster.key');

        $response = (app()->environment('local') ? Http::withoutVerifying() : Http::new())->get("https://app.ticketmaster.com/discovery/v2/events/{$id}.json", [
            'apikey' => $apiKey,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Évènement introuvable'], 404);
        }

        $event = $response->json();

        return response()->json([
            'id' => $event['id'],
            'title' => $event['name'],
            'date' => $event['dates']['start']['localDate'] ?? null,
            'time' => $event['dates']['start']['localTime'] ?? null,
            'city' => $event['_embedded']['venues'][0]['city']['name'] ?? null,
            'venue' => $event['_embedded']['venues'][0]['name'] ?? null,
            'latitude' => $event['_embedded']['venues'][0]['location']['latitude'] ?? null,
            'longitude' => $event['_embedded']['venues'][0]['location']['longitude'] ?? null,
            'image_url' => $event['images'][0]['url'] ?? null,
            'ticket_url' => $event['url'] ?? null,
            'category' => $event['classifications'][0]['segment']['name'] ?? null,
            'description' => $event['info'] ?? $event['pleaseNote'] ?? null,
        ]);
    }

    private function getCoordinates(array $venue): array
    {
        $latitude = $venue['location']['latitude'] ?? null;
        $longitude = $venue['location']['longitude'] ?? null;

        if ($latitude && $longitude) {
            return [$latitude, $longitude];
        }

        $address = collect([
            $venue['address']['line1'] ?? null,
            $venue['city']['name'] ?? null,
            $venue['country']['name'] ?? null,
        ])->filter()->implode(', ');

        if (!$address) {
            return [null, null];
        }

        $response = (app()->environment('local') ? Http::withoutVerifying() : Http::new())
            ->withHeaders(['User-Agent' => 'EventMap/1.0'])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

        if ($response->ok() && count($response->json()) > 0) {
            $result = $response->json()[0];
            return [$result['lat'], $result['lon']];
        }

        return [null, null];
    }
}