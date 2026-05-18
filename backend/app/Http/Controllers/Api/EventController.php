<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $apiKey = env('TICKETMASTER_API_KEY');

        $response = Http::withoutVerifying()->get('https://app.ticketmaster.com/discovery/v2/events.json', [
            'apikey' => $apiKey,
            'countryCode' => 'BE',
            'size' => 20,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'API Ticketmaster indisponible'], 503);
        }

        $events = collect($response->json('_embedded.events') ?? [])
            ->map(function ($event) {
                return [
                    'id' => $event['id'],
                    'title' => $event['name'],
                    'date' => $event['dates']['start']['localDate'] ?? null,
                    'city' => $event['_embedded']['venues'][0]['city']['name'] ?? null,
                    'latitude' => $event['_embedded']['venues'][0]['location']['latitude'] ?? null,
                    'longitude' => $event['_embedded']['venues'][0]['location']['longitude'] ?? null,
                    'image_url' => $event['images'][0]['url'] ?? null,
                    'ticket_url' => $event['url'] ?? null,
                    'category' => $event['classifications'][0]['segment']['name'] ?? null,
                ];
            });

        return response()->json($events);
    }
}