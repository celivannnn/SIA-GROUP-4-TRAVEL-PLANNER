<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TravelHotelController extends Controller
{
    public function searchHotels(Request $request)
    {
        $city = $request->input('city');
        $checkInDate = $request->input('check_in');
        $checkOutDate = $request->input('check_out');

        $apiToken = config('services.travelpayouts.api_token');
        $unsplashKey = config('services.unsplash.access_key');

        // Fetch hotels from Travelpayouts
        $hotelResponse = Http::withHeaders([
            'Authorization' => 'Token ' . $apiToken,
        ])->get('https://engine.hotellook.com/api/v2/cache.json', [
            'location' => $city,
            'checkIn' => $checkInDate,
            'checkOut' => $checkOutDate,
            'currency' => 'USD',
            'limit' => 10,
        ]);

        if (!$hotelResponse->successful()) {
            return response()->json(['error' => 'Failed to fetch hotels'], 500);
        }

        $hotels = $hotelResponse->json();

        // Attach images using Unsplash
        $hotelsWithImages = array_map(function ($hotel) use ($unsplashKey) {
            $image = 'https://via.placeholder.com/300x200?text=No+Image';

            if (!empty($hotel['hotelName'])) {
                $imageResponse = Http::get('https://api.unsplash.com/search/photos', [
                    'query' => $hotel['hotelName'],
                    'client_id' => $unsplashKey,
                    'per_page' => 1,
                    'orientation' => 'landscape',
                ]);

                if ($imageResponse->successful() && isset($imageResponse['results'][0]['urls']['regular'])) {
                    $image = $imageResponse['results'][0]['urls']['regular'];
                }
            }

            return [
                'hotel_id' => $hotel['hotelId'] ?? null,
                'name' => $hotel['hotelName'] ?? 'Unnamed hotel',
                'price' => $hotel['price'] ?? 0,
                'stars' => $hotel['stars'] ?? 0,
                'location' => $hotel['location'] ?? ['lat' => null, 'lon' => null],
                'url' => $hotel['url'] ?? null,
                'image' => $image,
            ];
        }, $hotels);

        return response()->json([
            'city' => $city,
            'check_in' => $checkInDate,
            'check_out' => $checkOutDate,
            'hotels' => $hotelsWithImages,
            'total' => count($hotelsWithImages),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
