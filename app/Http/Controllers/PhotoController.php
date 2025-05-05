<?php

namespace App\Http\Controllers;
use App\Services\PhotoService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PhotoController extends Controller
{
    public function getPhotos(Request $request)
    {
        $city = $request->query('city', 'Manila');

        $response = Http::withHeaders([
            'Authorization' => 'Client-ID ' . config('services.unsplash.key'),
        ])->get('https://api.unsplash.com/search/photos', [
            'query' => $city,
            'per_page' => 5,
        ]);

        return response()->json($response->json());
    }
}
