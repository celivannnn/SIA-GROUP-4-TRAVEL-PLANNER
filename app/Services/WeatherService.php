<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Fetch weather data for a given city using OpenWeather API.
     *
     * @param string $city City name to fetch weather for (default: Manila)
     * @return JsonResponse
     */
    public function getWeather(string $city = 'Manila'): JsonResponse
    {
        if (empty(trim($city))) {
            return response()->json(['error' => 'City name cannot be empty'], 400);
        }

        $apiKey = config('services.openweather.key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'OpenWeather API key not configured'], 500);
        }

        try {
            $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            if ($response->ok()) {
                $data = $response->json();
                Log::info('Weather API Response: ', $data); // Debug log

                if (!isset($data['main']) || !isset($data['weather'])) {
                    return response()->json(['error' => 'Invalid response from weather API'], 500);
                }

                $weather = [
                    'city' => $data['name'] ?? $city,
                    'temperature' => $data['main']['temp'] ?? null,
                    'feels_like' => $data['main']['feels_like'] ?? null,
                    'humidity' => $data['main']['humidity'] ?? null,
                    'weather' => $data['weather'][0]['main'] ?? 'Unknown',
                    'description' => $data['weather'][0]['description'] ?? 'No description',
                    'wind_speed' => $data['wind']['speed'] ?? null,
                    'timestamp' => now()->toDateTimeString()
                ];

                return response()->json($weather, 200);
            }

            switch ($response->status()) {
                case 400:
                    return response()->json(['error' => 'Bad request: Invalid city name format'], 400);
                case 401:
                    return response()->json(['error' => 'Unauthorized: Invalid API key'], 401);
                case 404:
                    return response()->json(['error' => 'City not found'], 404);
                default:
                    return response()->json(['error' => 'Failed to fetch weather data', 'status' => $response->status()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error', 'message' => $e->getMessage()], 500);
        }
    }
}