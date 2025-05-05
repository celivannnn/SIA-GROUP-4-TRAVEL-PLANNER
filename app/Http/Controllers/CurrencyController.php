<?php

namespace App\Http\Controllers;
use App\Services\CurrencyService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function convert(Request $request)
    {
        $from = $request->query('from', 'USD');
        $to = $request->query('to', 'EUR');
        $amount = $request->query('amount', 1);

        $response = Http::get("https://v6.exchangerate-api.com/v6/" . env('CURRENCY_API_KEY') . "/pair/{$from}/{$to}");

        if ($response->ok()) {
            $rate = $response['conversion_rate'];
            $converted = $rate * $amount;

            return response()->json([
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'rate' => $rate,
                'converted' => $converted
            ]);
        } else {
            return response()->json(['error' => 'Failed to fetch currency data'], 500);
        }
    }
}
