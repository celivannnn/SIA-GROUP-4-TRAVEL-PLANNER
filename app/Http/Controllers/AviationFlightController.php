<?php
namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Services\AviationstackService;
use Illuminate\Http\Request;

class AviationFlightController extends Controller
{
    protected $aviationstack;

    public function __construct(AviationstackService $aviationstack)
    {
        $this->aviationstack = $aviationstack;
    }

    public function search(Request $request)
    {
        $from = $request->input('from'); // e.g. JFK

        if (!$from) {
            return response()->json(['error' => 'Missing "from" parameter'], 400);
        }

        $result = $this->aviationstack->searchFlights($from);

        return response()->json($result);
    }
}
