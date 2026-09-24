<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    private function httpClient()
    {
        return app()->environment('local') ? Http::withoutVerifying() : Http::withOptions([]);
    }

    public function index(Request $request)
    {
        $params = [
            'apikey' => config('services.ticketmaster.key'),
            'size' => 100,
        ];

        if ($request->filled('keyword')) {
            $params['keyword'] = $request->keyword;
        }

        if ($request->filled('city')) {
            $params['city'] = $request->city;
        }

        if ($request->filled('category')) {
            $params['classificationName'] = $request->category;
        }

        if ($request->filled('startDate')) {
            $params['startDateTime'] = $request->startDate . 'T00:00:00Z';
        }

        if ($request->filled('endDate')) {
            $params['endDateTime'] = $request->endDate . 'T23:59:59Z';
        }

        if ($request->has('bbox')) {
            [$south, $west, $north, $east] = explode(',', $request->bbox);
            $params['geoPoint'] = round(($south + $north) / 2, 6) . ',' . round(($west + $east) / 2, 6);
            $params['radius'] = max(5, (int) ceil(max($north - $south, $east - $west) / 2 * 111));
            $params['unit'] = 'km';
        } elseif (!$request->filled('keyword') && !$request->filled('city')) {
            $params['countryCode'] = 'BE';
        }

        // Évènements Ticketmaster (mis en cache 15 min)
        $cacheKey = 'ticketmaster:events:v3:' . md5(json_encode($params));
        $ticketmasterEvents = Cache::get($cacheKey);

        if ($ticketmasterEvents === null) {
            $response = $this->httpClient()->get('https://app.ticketmaster.com/discovery/v2/events.json', $params);

            if ($response->failed()) {
                return response()->json(['error' => 'API Ticketmaster indisponible'], 503);
            }

            $ticketmasterEvents = collect($response->json('_embedded.events') ?? [])
                ->groupBy(function ($event) {
                    $attractionId = $event['_embedded']['attractions'][0]['id'] ?? $event['id'];
                    $venueId = $event['_embedded']['venues'][0]['id'] ?? 'no-venue';
                    return $attractionId . '|' . $venueId;
                })
                ->map(function ($group) {
                    $first = $group->first();
                    $venue = $first['_embedded']['venues'][0] ?? [];
                    [$lat, $lng] = $this->getCoordinates($venue);

                    $attractionId = $first['_embedded']['attractions'][0]['id'] ?? $first['id'];
                    $venueId = $venue['id'] ?? 'no-venue';

                    return [
                        'id' => $attractionId . '|' . $venueId,
                        'source' => 'ticketmaster',
                        'title' => trim(explode('|', $first['name'])[0]),
                        'date' => $first['dates']['start']['localDate'] ?? null,
                        'city' => $venue['city']['name'] ?? null,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'image_url' => $first['images'][0]['url'] ?? null,
                        'ticket_url' => $first['url'] ?? null,
                        'category' => $first['classifications'][0]['segment']['name'] ?? null,
                        'sub_events' => $group->map(function ($e) {
                            return [
                                'id' => $e['id'],
                                'name' => $e['name'],
                                'date' => $e['dates']['start']['localDate'] ?? null,
                                'ticket_url' => $e['url'] ?? null,
                            ];
                        })->values()->toArray(),
                    ];
                })
                ->values()
                ->toArray();

            Cache::put($cacheKey, $ticketmasterEvents, now()->addMinutes(15));
        }

        // Évènements créés par les utilisateurs (jamais en cache)
        $userEvents = $this->getUserEvents($request);

        $events = collect($ticketmasterEvents)
            ->concat($userEvents)
            ->sortBy('date')
            ->values();

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 20);

        $paginated = $events->forPage($page, $perPage)->values();

        return response()->json([
            'data' => $paginated,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $events->count(),
            'last_page' => (int) max(1, ceil($events->count() / $perPage)),
        ]);
    }

    public function show(string $id)
    {
        // Évènement créé par un utilisateur
        if (str_starts_with($id, 'user-')) {
            $event = UserEvent::with('user')->find((int) substr($id, 5));

            if (!$event) {
                return response()->json(['error' => 'Évènement introuvable'], 404);
            }

            return response()->json([
                'id' => $id,
                'source' => 'user',
                'author' => $event->user?->name,
                'title' => $event->title,
                'city' => $event->city,
                'venue' => $event->venue,
                'address' => $event->address,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'image_url' => $event->image ? asset('storage/' . $event->image) : null,
                'category' => $event->category,
                'description' => $event->description,
                'sub_events' => [
                    [
                        'id' => $id,
                        'name' => $event->title,
                        'date' => $event->date->format('Y-m-d'),
                        'time' => $event->time ? substr($event->time, 0, 5) : null,
                        'ticket_url' => null,
                    ]
                ],
            ]);
        }

        // Évènement Ticketmaster
        [$attractionId, $venueId] = array_pad(explode('|', $id, 2), 2, null);

        $cacheKey = 'ticketmaster:event:v2:' . $id;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return response()->json($cached);
        }

        $response = $this->httpClient()->get('https://app.ticketmaster.com/discovery/v2/events.json', [
            'apikey' => config('services.ticketmaster.key'),
            'attractionId' => $attractionId,
            'size' => 100,
        ]);

        $events = collect($response->json('_embedded.events') ?? []);

        // Ne garder que les events du même lieu (venueId)
        if ($venueId && $venueId !== 'no-venue') {
            $events = $events->filter(function ($e) use ($venueId) {
                return ($e['_embedded']['venues'][0]['id'] ?? null) === $venueId;
            })->values();
        }

        // Fallback : pas un id d'attraction connu, ou rien trouvé après filtrage
        if ($events->isEmpty()) {
            $single = $this->httpClient()->get("https://app.ticketmaster.com/discovery/v2/events/{$attractionId}.json", [
                'apikey' => config('services.ticketmaster.key'),
            ]);

            if ($single->failed()) {
                return response()->json(['error' => 'Évènement introuvable'], 404);
            }

            $events = collect([$single->json()]);
        }

        $first = $events->first();
        $venue = $first['_embedded']['venues'][0] ?? [];

        $result = [
            'id' => $id,
            'source' => 'ticketmaster',
            'title' => trim(explode('|', $first['name'])[0]),
            'city' => $venue['city']['name'] ?? null,
            'venue' => $venue['name'] ?? null,
            'latitude' => $venue['location']['latitude'] ?? null,
            'longitude' => $venue['location']['longitude'] ?? null,
            'image_url' => $first['images'][0]['url'] ?? null,
            'category' => $first['classifications'][0]['segment']['name'] ?? null,
            'description' => $first['info'] ?? $first['pleaseNote'] ?? null,
            'sub_events' => $events->map(function ($e) {
                return [
                    'id' => $e['id'],
                    'name' => $e['name'],
                    'date' => $e['dates']['start']['localDate'] ?? null,
                    'time' => $e['dates']['start']['localTime'] ?? null,
                    'ticket_url' => $e['url'] ?? null,
                ];
            })->values()->toArray(),
        ];

        Cache::put($cacheKey, $result, now()->addMinutes(15));

        return response()->json($result);
    }

    private function getUserEvents(Request $request): array
    {
        $query = UserEvent::query()->whereDate('date', '>=', today());

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        if ($request->has('bbox')) {
            [$south, $west, $north, $east] = explode(',', $request->bbox);
            $query->whereBetween('latitude', [$south, $north])
                ->whereBetween('longitude', [$west, $east]);
        }

        return $query->get()->map(function ($event) {
            $id = 'user-' . $event->id;

            return [
                'id' => $id,
                'source' => 'user',
                'title' => $event->title,
                'date' => $event->date->format('Y-m-d'),
                'city' => $event->city,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'image_url' => $event->image ? asset('storage/' . $event->image) : null,
                'ticket_url' => null,
                'category' => $event->category,
                'sub_events' => [
                    [
                        'id' => $id,
                        'name' => $event->title,
                        'date' => $event->date->format('Y-m-d'),
                        'ticket_url' => null,
                    ]
                ],
            ];
        })->toArray();
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

        $response = $this->httpClient()
            ->timeout(3)
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