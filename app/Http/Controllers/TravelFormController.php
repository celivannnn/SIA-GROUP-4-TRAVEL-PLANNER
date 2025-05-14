<?php

namespace App\Http\Controllers;

use App\Services\TravelFormService;
use Illuminate\Http\Request;

class TravelFormController extends Controller
{
    protected $travelFormService;

    public function __construct(TravelFormService $travelFormService)
    {
        $this->travelFormService = $travelFormService;
    }

    public function index()
    {
        $forms = $this->travelFormService->getAll();
        return response()->json($forms);
    }

    public function show($id)
    {
        $form = $this->travelFormService->getById($id);
        return response()->json($form);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'country' => 'required|string',
            'travel_date' => 'required|date',
            'budget' => 'required|numeric',
            'currency' => 'required|string',
            'days' => 'required|integer',
        ]);

        $created = $this->travelFormService->create($data);
        return response()->json($created, 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'country' => 'sometimes|required|string',
            'travel_date' => 'sometimes|required|date',
            'budget' => 'sometimes|required|numeric',
            'currency' => 'sometimes|required|string',
            'days' => 'sometimes|required|integer',
        ]);

        $updated = $this->travelFormService->update($id, $data);
        return response()->json($updated);
    }

    public function destroy($id)
    {
        $deleted = $this->travelFormService->delete($id);
        return response()->json($deleted);
    }
}
