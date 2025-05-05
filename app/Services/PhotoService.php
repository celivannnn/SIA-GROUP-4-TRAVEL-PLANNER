<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class PhotoService
{
    /**
     * Fetch photos for a given city from Unsplash API.
     *
     * @param string $city City name to search for photos (default: Manila)
     * @return JsonResponse
     */
    public function getPhotos(string $city = 'Manila'): JsonResponse
    {
        // Validate city input
        if (empty(trim($city))) {
            return response()->json([
                'error' => 'City name cannot be empty'
            ], 400);
        }

        // Check if API key is configured
        $apiKey = config('services.unsplash.key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Unsplash API key not configured'
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Client-ID ' . $apiKey,
            ])->get('https://api.unsplash.com/search/photos', [
                'query' => $city,
                'per_page' => 5,
            ]);

            if ($response->ok()) {
                $data = $response->json();

                // Check if results exist in response
                if (!isset($data['results'])) {
                    return response()->json([
                        'error' => 'Invalid response from Unsplash API'
                    ], 500);
                }

                // Format response data
                $photos = array_map(function ($photo) {
                    return [
                        'id' => $photo['id'],
                        'url' => $photo['urls']['regular'],
                        'description' => $photo['description'] ?? 'No description available',
                        'user' => $photo['user']['name'],
                        'link' => $photo['links']['html']
                    ];
                }, $data['results']);

                return response()->json([
                    'city' => $city,
                    'photos' => $photos,
                    'total' => $data['total'],
                    'timestamp' => now()->toDateTimeString()
                ], 200);
            }

            // Handle specific HTTP status codes
            switch ($response->status()) {
                case 400:
                    return response()->json([
                        'error' => 'Bad request: Invalid query parameters'
                    ], 400);
                case 401:
                    return response()->json([
                        'error' => 'Unauthorized: Invalid API key'
                    ], 401);
                case 404:
                    return response()->json([
                        'error' => 'Resource not found'
                    ], 404);
                default:
                    return response()->json([
                        'error' => 'Failed to fetch photos',
                        'status' => $response->status()
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