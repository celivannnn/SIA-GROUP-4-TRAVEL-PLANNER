<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class AmadeusService
{
    protected $apiKey;
    protected $apiSecret;
    protected $accessToken;

    public function __construct()
    {
        $this->apiKey = config('services.amadeus.key');
        $this->apiSecret = config('services.amadeus.secret');

        if (empty($this->apiKey) || empty($this->apiSecret)) {
            throw new \Exception('Amadeus API key or secret not configured');
        }

        $this->authenticate();
    }

    protected function authenticate()
    {
        try {
            $response = Http::asForm()->post('https://test.api.amadeus.com/v1/security/oauth2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->apiKey,
                'client_secret' => $this->apiSecret,
            ]);

            if ($response->ok()) {
                $this->accessToken = $response->json()['access_token'] ?? null;
                if (empty($this->accessToken)) {
                    throw new \Exception('Failed to retrieve access token');
                }
            } else {
                throw new \Exception('Authentication failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            throw new \Exception('Authentication error: ' . $e->getMessage());
        }
    }

    /**
     * Search for flight offers using Amadeus API.
     *
     * @param string $origin IATA code for origin airport
     * @param string $destination IATA code for destination airport
     * @param string $departureDate Departure date (format: YYYY-MM-DD)
     * @return JsonResponse
     */
    public function searchFlights(string $origin, string $destination, string $departureDate): JsonResponse
    {
        // Validate inputs
        if (!preg_match('/^[A-Z]{3}$/', $origin) || !preg_match('/^[A-Z]{3}$/', $destination)) {
            return response()->json([
                'error' => 'Invalid IATA code format. Use 3-letter airport codes'
            ], 400);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $departureDate)) {
            return response()->json([
                'error' => 'Invalid date format. Use YYYY-MM-DD'
            ], 400);
        }

        if (empty($this->accessToken)) {
            return response()->json([
                'error' => 'Authentication token not available'
            ], 401);
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get('https://test.api.amadeus.com/v2/shopping/flight-offers', [
                    'originLocationCode' => $origin,
                    'destinationLocationCode' => $destination,
                    'departureDate' => $departureDate,
                    'adults' => 1,
                    'max' => 5
                ]);

            if ($response->ok()) {
                $data = $response->json();

                if (!isset($data['data']) || empty($data['data'])) {
                    return response()->json([
                        'error' => 'No flights found'
                    ], 404);
                }

                // Format response data
                $flights = array_map(function ($flight) {
                    return [
                        'id' => $flight['id'] ?? null,
                        'price' => $flight['price']['total'] ?? 'N/A',
                        'currency' => $flight['price']['currency'] ?? 'USD',
                        'duration' => $flight['itineraries'][0]['duration'] ?? 'N/A',
                        'segments' => array_map(function ($segment) {
                            return [
                                'departure' => $segment['departure']['iataCode'] ?? 'N/A',
                                'arrival' => $segment['arrival']['iataCode'] ?? 'N/A',
                                'departureTime' => $segment['departure']['at'] ?? 'N/A',
                                'arrivalTime' => $segment['arrival']['at'] ?? 'N/A'
                            ];
                        }, $flight['itineraries'][0]['segments'])
                    ];
                }, $data['data']);

                return response()->json([
                    'origin' => $origin,
                    'destination' => $destination,
                    'departure_date' => $departureDate,
                    'flights' => $flights,
                    'total' => count($flights),
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
                        'error' => 'Unauthorized: Invalid or expired token'
                    ], 401);
                case 404:
                    return response()->json([
                        'error' => 'No flights found for this route'
                    ], 404);
                default:
                    return response()->json([
                        'error' => 'Failed to fetch flights',
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