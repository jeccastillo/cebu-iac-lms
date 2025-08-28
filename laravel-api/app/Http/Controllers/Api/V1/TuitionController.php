<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TuitionComputeRequest;
use App\Http\Resources\TuitionBreakdownResource;
use App\Services\TuitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TuitionController extends Controller
{
    /**
     * GET /api/v1/tuition/compute
     * Query params:
     * - student_number (string, required)
     * - term (int syid, required)
     * - discount_id (int|null, optional) - compute using specific discount only (parity hook)
     * - scholarship_id (int|null, optional) - compute using specific scholarship only (parity hook)
     */
    public function compute(TuitionComputeRequest $request, TuitionService $service): JsonResponse
    {
        $studentNumber = $request->string('student_number')->trim();
        $syid = (int) $request->input('term');
        $discountId = $request->input('discount_id');
        $scholarshipId = $request->input('scholarship_id');

        try {
            $breakdown = $service->compute(
                $studentNumber,
                $syid,
                $discountId ? (int) $discountId : null,
                $scholarshipId ? (int) $scholarshipId : null
            );

            return response()->json(new TuitionBreakdownResource($breakdown));
        } catch (\InvalidArgumentException $e) {
            // Validation/lookup errors (e.g., student not found, registration/tuition year missing)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\RuntimeException $e) {
            // Computation logic errors or missing schema assumptions
            Log::warning('Tuition compute runtime error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to compute tuition at this time.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Tuition compute unexpected error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error computing tuition.',
            ], 500);
        }
    }
}
