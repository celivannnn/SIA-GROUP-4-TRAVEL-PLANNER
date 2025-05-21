<?php

namespace App\Http\Controllers;

use App\Services\TravelFormService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TravelFormController extends Controller
{
    protected $travelFormService;

    public function __construct(TravelFormService $travelFormService)
    {
        $this->travelFormService = $travelFormService;
        $this->middleware('auth:sanctum'); // Protect routes, require auth
    }

    public function index(Request $request): JsonResponse
    {
        $travelForms = $this->travelFormService->getAllForUser($request->user()->id);

        return $this->successResponse($travelForms, 'Travel forms retrieved successfully');
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $travelForm = $this->travelFormService->getByIdForUser($id, $request->user()->id);
            return $this->successResponse($travelForm, 'Travel form retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Travel form not found or access denied', 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'travel_date' => 'required|date',
            'budget' => 'required|numeric|min:0',
            'days' => 'required|integer|min:1',
            'currency' => 'required|string|max:3',
        ]);

        $travelForm = $this->travelFormService->createForUser($validated, $request->user()->id);

        return $this->successResponse($travelForm, 'Travel form created successfully', 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:100',
            'travel_date' => 'sometimes|date',
            'budget' => 'sometimes|numeric|min:0',
            'days' => 'sometimes|integer|min:1',
            'currency' => 'sometimes|string|max:3',
        ]);

        try {
            $travelForm = $this->travelFormService->updateForUser($id, $validated, $request->user()->id);
            return $this->successResponse($travelForm, 'Travel form updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Travel form not found or access denied', 404);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $this->travelFormService->deleteForUser($id, $request->user()->id);
            return $this->successResponse(null, 'Travel form deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Travel form not found or access denied', 404);
        }
    }

    // JSON response helpers

    protected function successResponse($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }
}
