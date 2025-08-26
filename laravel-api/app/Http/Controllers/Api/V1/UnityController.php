<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UnityAdvisingRequest;
use App\Http\Resources\TuitionBreakdownResource;
use App\Services\TuitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnityController extends Controller
{
    protected TuitionService $tuition;

    public function __construct(TuitionService $tuition)
    {
        $this->tuition = $tuition;
    }

    /**
     * POST /api/v1/unity/advising
     * Body: UnityAdvisingRequest
     * Returns a placeholder advising plan echoing input for now.
     */
    public function advising(UnityAdvisingRequest $request): JsonResponse
    {
        $payload = $request->validated();

        // Placeholder: echo subjects back as the "plan"
        $plan = [
            'student_number' => $payload['student_number'],
            'program_id'     => $payload['program_id'],
            'term'           => $payload['term'],
            'subjects'       => $payload['subjects'],
            'notes'          => 'Advising logic not yet implemented. This is a placeholder response.',
        ];

        return response()->json([
            'success' => true,
            'data'    => $plan,
        ]);
    }

    /**
     * POST /api/v1/unity/enlist
     * Placeholder endpoint - not yet implemented.
     */
    public function enlist(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Enlist not implemented',
        ], 501);
    }

    /**
     * POST /api/v1/unity/tag-status
     * Placeholder endpoint - not yet implemented.
     */
    public function tagStatus(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tag status not implemented',
        ], 501);
    }

    /**
     * POST /api/v1/unity/tuition-preview
     * Body:
     *  - student_number: string
     *  - program_id: int
     *  - term: string
     *  - subjects: array of { subject_id: int, section?: string }
     * Returns TuitionBreakdownResource (placeholder breakdown for now).
     */
    public function tuitionPreview(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number'        => 'required|string',
            'program_id'            => 'required|integer',
            'term'                  => 'required|string',
            'subjects'              => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|integer',
            'subjects.*.section'    => 'nullable|string',
        ]);

        $breakdown = $this->tuition->preview($payload);

        return response()->json([
            'success' => true,
            'data'    => new TuitionBreakdownResource($breakdown),
        ]);
    }
}
