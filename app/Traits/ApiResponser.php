<?php

namespace App\Traits;

trait ApiResponser
{
    /**
     * Return a success JSON response with travel form data (collection).
     *
     * @param mixed $travelForms
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successTravelFormResponse($travelForms, $message = 'Travel form data retrieved successfully', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'travel_forms' => $travelForms
            ]
        ], $code);
    }

    /**
     * Return a success JSON response for a single travel form.
     *
     * @param mixed $travelForm
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successSingleTravelFormResponse($travelForm, $message = 'Travel form processed successfully', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'travel_form' => $travelForm
            ]
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message = 'Error processing travel form', $code = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}