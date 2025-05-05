<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\TravelHotelService;

class TravelHotelController extends Controller
{
    public function searchHotels(Request $request)
    {
        $city = $request->input('city'); // city you want to search
        $checkInDate = $request->input('check_in'); // format: 2025-05-01
        $checkOutDate = $request->input('check_out'); // format: 2025-05-05

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . config('services.travelpayouts.api_token'),
        ])->get('https://engine.hotellook.com/api/v2/cache.json', [
            'location' => $city,
            'checkIn' => $checkInDate,
            'checkOut' => $checkOutDate,
            'currency' => 'USD',
            'limit' => 10,
        ]);

        return response()->json(json_decode($response->body()));
    }
}
