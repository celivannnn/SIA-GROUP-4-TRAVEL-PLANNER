<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class TravelHotelService
{
    /**
     * Search hotels for a given city and date range using Travelpayouts API.
     *
     * @param string $city City to search hotels for
     * @param string $checkInDate Check-in date (format: YYYY-MM-DD)
     * @param string $checkOutDate Check-out date (format: YYYY-MM-DD)
     * @return JsonResponse
     */
    public function searchHotels(string $city, string $checkInDate, string $checkOutDate): JsonResponse
    {
        // Validate inputs
        if (empty(trim($city))) {
            return response()->json([
                'error' => 'City name cannot be empty'
            ], 400);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkInDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkOutDate)) {
            return response()->json([
                'error' => 'Invalid date format. Use YYYY-MM-DD'
            ], 400);
        }

        // Check if API token is configured
        $apiToken = config('services.travelpayouts.api_token');
        if (empty($apiToken)) {
            return response()->json([
                'error' => 'Travelpayouts API token not configured'
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $apiToken,
            ])->get('https://engine.hotellook.com/api/v2/cache.json', [
                'location' => $city,
                'checkIn' => $checkInDate,
                'checkOut' => $checkOutDate,
                'currency' => 'USD',
                'limit' => 10,
            ]);

            if ($response->ok()) {
                $data = json_decode($response->body(), true);

                if (empty($data) || !isset($data['hotels'])) {
                    return response()->json([
                        'error' => 'No hotels found or invalid response'
                    ], 404);
                }

                // Format response data
                $hotels = array_map(function ($hotel) {
                    return [
                        'hotel_id' => $hotel['hotelId'] ?? null,
                        'name' => $hotel['hotelName'] ?? 'Unnamed hotel',
                        'price' => $hotel['price'] ?? 0,
                        'stars' => $hotel['stars'] ?? 0,
                        'location' => $hotel['location'] ?? ['lat' => null, 'lon' => null],
                        'url' => $hotel['url'] ?? null
                    ];
                }, $data['hotels']);

                return response()->json([
                    'city' => $city,
                    'check_in' => $checkInDate,
                    'check_out' => $checkOutDate,
                    'hotels' => $hotels,
                    'total' => count($hotels),
                    'timestamp' => now()->toDateTimeString()
                ], 200);
            }

            // Handle specific HTTP status codes
            switch ($response->status()) {
                case 400:
                    return response()->json([
                        'error' => 'Bad request: Invalid search parameters'
                    ], 400);
                case 401:
                    return response()->json([
                        'error' => 'Unauthorized: Invalid API token'
                    ], 401);
                case 404:
                    return response()->json([
                        'error' => 'No hotels found for this location'
                    ], 404);
                default:
                    return response()->json([
                        'error' => 'Failed to fetch hotels',
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