<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ScholarshipService;

class ScholarshipController extends Controller
{
    protected ScholarshipService $scholarships;

    public function __construct(ScholarshipService $scholarships)
    {
        $this->scholarships = $scholarships;
    }

    /**
     * GET /api/v1/scholarships
     * Optional filters:
     *  - status: active|inactive
     *  - deduction_type: scholarship|discount
     *  - deduction_from: in-house|external
     *  - q: search by name (LIKE)
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'status'          => 'sometimes|in:active,inactive',
            'deduction_type'  => 'sometimes|in:scholarship,discount',
            'deduction_from'  => 'sometimes|in:in-house,external',
            'q'               => 'sometimes|string',
        ]);

        $rows = $this->scholarships->list($payload);

        return response()->json([
            'success' => true,
            'data'    => ScholarshipResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/scholarships/assigned
     * Query:
     *  - syid: int (required)
     *  - student_id?: int OR student_number?: string (at least one required)
     *
     * Returns assigned scholarships and discounts for a student in a term.
     */
    public function assigned(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'syid'           => 'required|integer',
            'student_id'     => 'sometimes|integer',
            'student_number' => 'sometimes|string',
        ]);

        if (empty($payload['student_id']) && empty($payload['student_number'])) {
            return response()->json([
                'success' => false,
                'message' => 'Provide either student_id or student_number'
            ], 422);
        }

        $out = $this->scholarships->assigned(
            (int) $payload['syid'],
            isset($payload['student_id']) ? (int) $payload['student_id'] : null,
            isset($payload['student_number']) ? (string) $payload['student_number'] : null
        );

        if ($out['student'] === null) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student'      => $out['student'],
                'scholarships' => $out['scholarships'],
                'discounts'    => $out['discounts'],
            ]
        ]);
    }

    /**
     * GET /api/v1/scholarships/enrolled
     * Query:
     *  - syid: int (required)
     *  - q?: string (optional filter by student number or last name)
     *
     * Returns students who have any scholarship/discount assigned for the term,
     * with aggregated scholarship/discount names.
     */
    public function enrolled(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'syid' => 'required|integer',
            'q'    => 'sometimes|string',
        ]);

        $result = $this->scholarships->enrolled((int) $payload['syid'], $payload['q'] ?? null);

        return response()->json([
            'success' => true,
            'data'    => [
                'students' => $result,
            ],
        ]);
    }

    /**
     * POST /api/v1/scholarships/upsert (stub)
     * DELETE /api/v1/scholarships/{id} (stub)
     * For this pass, we return 501 Not Implemented to keep read-only baseline first.
     */
    public function upsert(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Not Implemented'
        ], 501);
    }

    public function delete(int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Not Implemented'
        ], 501);
    }
}
