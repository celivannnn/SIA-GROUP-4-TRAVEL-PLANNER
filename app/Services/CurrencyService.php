<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class CurrencyService
{
    /**
     * Convert currency from one type to another.
     *
     * @param string $from Source currency code (default: USD)
     * @param string $to Target currency code (default: EUR)
     * @param float $amount Amount to convert (default: 1)
     * @return JsonResponse
     */
    public function convert(string $from = 'USD', string $to = 'EUR', float $amount = 1): JsonResponse
    {
        // Validate currency codes
        if (!$this->isValidCurrencyCode($from) || !$this->isValidCurrencyCode($to)) {
            return response()->json([
                'error' => 'Invalid currency code provided',
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'JPY', 'CAD'] // Example list
            ], 400);
        }

        // Validate amount
        if ($amount <= 0) {
            return response()->json([
                'error' => 'Amount must be greater than zero'
            ], 400);
        }

        // Check if API key is configured
        $apiKey = env('CURRENCY_API_KEY');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'API key not configured'
            ], 500);
        }

        try {
            $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$from}/{$to}");

            if ($response->ok()) {
                $data = $response->json();

                // Check if conversion_rate exists in response
                if (!isset($data['conversion_rate'])) {
                    return response()->json([
                        'error' => 'Invalid response from currency API'
                    ], 500);
                }

                $rate = $data['conversion_rate'];
                $converted = $rate * $amount;

                return response()->json([
                    'from' => $from,
                    'to' => $to,
                    'amount' => $amount,
                    'rate' => $rate,
                    'converted' => round($converted, 4),
                    'timestamp' => now()->toDateTimeString()
                ], 200);
            }

            // Handle specific HTTP status codes
            switch ($response->status()) {
                case 401:
                    return response()->json([
                        'error' => 'Unauthorized: Invalid API key'
                    ], 401);
                case 404:
                    return response()->json([
                        'error' => 'Currency pair not found'
                    ], 404);
                default:
                    return response()->json([
                        'error' => 'Failed to fetch currency data',
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

    /**
     * Validate currency code format.
     *
     * @param string $code
     * @return bool
     */
    private function isValidCurrencyCode(string $code): bool
    {
        // Basic validation: 3-letter uppercase code
        return preg_match('/^[A-Z]{3}$/', $code) === 1;
    }
}