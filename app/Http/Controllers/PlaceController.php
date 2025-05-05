<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\PlaceService;

class PlaceController extends Controller
{
    public function getPlacesByCity($city)
    {
        $apiKey = config('services.geoapify.key');

        // Step 1: Geocode city to get lat/lon
        $geocode = Http::get("https://api.geoapify.com/v1/geocode/search", [
            'text' => $city,
            'apiKey' => $apiKey
        ]);

        $geoData = $geocode->json();

        if (empty($geoData['features'])) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $lat = $geoData['features'][0]['properties']['lat'];
        $lon = $geoData['features'][0]['properties']['lon'];

        // Step 2: Search for places near the coordinates
        $places = Http::get("https://api.geoapify.com/v2/places", [
            'categories' => 'tourism.sights',
            'filter' => "circle:$lon,$lat,5000",
            'limit' => 10,
            'apiKey' => $apiKey
        ]);

        return response()->json($places->json());
    }
}
