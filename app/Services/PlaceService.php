<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class PlaceService
{
    /**
     * Fetch tourist sights for a given city using Geoapify API.
     *
     * @param string $city City name to search for places
     * @return JsonResponse
     */
    public function getPlacesByCity(string $city): JsonResponse
    {
        // Validate city input
        if (empty(trim($city))) {
            return response()->json([
                'error' => 'City name cannot be empty'
            ], 400);
        }

        // Check if API key is configured
        $apiKey = config('services.geoapify.key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Geoapify API key not configured'
            ], 500);
        }

        try {
            // Step 1: Geocode city to get lat/lon
            $geocode = Http::get("https://api.geoapify.com/v1/geocode/search", [
                'text' => $city,
                'apiKey' => $apiKey
            ]);

            if (!$geocode->ok()) {
                switch ($geocode->status()) {
                    case 400:
                        return response()->json([
                            'error' => 'Bad request: Invalid city name format'
                        ], 400);
                    case 401:
                        return response()->json([
                            'error' => 'Unauthorized: Invalid API key'
                        ], 401);
                    default:
                        return response()->json([
                            'error' => 'Failed to geocode city',
                            'status' => $geocode->status()
                        ], 500);
                }
            }

            $geoData = $geocode->json();

            if (empty($geoData['features'])) {
                return response()->json([
                    'error' => 'City not found'
                ], 404);
            }

            $lat = $geoData['features'][0]['properties']['lat'] ?? null;
            $lon = $geoData['features'][0]['properties']['lon'] ?? null;

            if (is_null($lat) || is_null($lon)) {
                return response()->json([
                    'error' => 'Unable to retrieve coordinates for city'
                ], 500);
            }

            // Step 2: Search for places near the coordinates
            $places = Http::get("https://api.geoapify.com/v2/places", [
                'categories' => 'tourism.sights',
                'filter' => "circle:$lon,$lat,5000",
                'limit' => 10,
                'apiKey' => $apiKey
            ]);

            if ($places->ok()) {
                $placesData = $places->json();

                if (!isset($placesData['features'])) {
                    return response()->json([
                        'error' => 'Invalid response from places API'
                    ], 500);
                }

                // Format response data
                $formattedPlaces = array_map(function ($place) {
                    return [
                        'name' => $place['properties']['name'] ?? 'Unnamed location',
                        'address' => $place['properties']['formatted'] ?? 'No address available',
                        'latitude' => $place['geometry']['coordinates'][1] ?? null,
                        'longitude' => $place['geometry']['coordinates'][0] ?? null,
                        'category' => $place['properties']['categories'][0] ?? 'tourism.sights'
                    ];
                }, $placesData['features']);

                return response()->json([
                    'city' => $city,
                    'places' => $formattedPlaces,
                    'total' => count($formattedPlaces),
                    'timestamp' => now()->toDateTimeString()
                ], 200);
            }

            // Handle specific HTTP status codes for places request
            switch ($places->status()) {
                case 400:
                    return response()->json([
                        'error' => 'Bad request: Invalid place search parameters'
                    ], 400);
                case 401:
                    return response()->json([
                        'error' => 'Unauthorized: Invalid API key'
                    ], 401);
                case 404:
                    return response()->json([
                        'error' => 'No places found for this location'
                    ], 404);
                default:
                    return response()->json([
                        'error' => 'Failed to fetch places',
                        'status' => $places->status()
                    ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}