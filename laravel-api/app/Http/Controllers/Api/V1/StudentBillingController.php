<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StudentBillingStoreRequest;
use App\Http\Requests\Api\V1\StudentBillingUpdateRequest;
use App\Services\StudentBillingService;
use App\Services\TuitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentBillingController extends Controller
{
    protected StudentBillingService $service;

    public function __construct(StudentBillingService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/finance/student-billing
     * Query:
     *  - student_number?: string (optional when student_id is provided)
     *  - student_id?: int
     *  - term: int (syid) required
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number' => ['sometimes', 'nullable', 'string'],
            'student_id'     => ['sometimes', 'nullable', 'integer'],
            'term'           => ['required', 'integer'],
        ]);

        $items = $this->service->list(
            $payload['student_number'] ?? null,
            isset($payload['student_id']) ? (int) $payload['student_id'] : null,
            (int) $payload['term']
        );

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * GET /api/v1/finance/student-billing/{id}
     */
    public function show(int $id): JsonResponse
    {
        $item = $this->service->get($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Student billing item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * POST /api/v1/finance/student-billing
     * Body (StudentBillingStoreRequest):
     *  - student_id: int
     *  - term: int (syid)
     *  - description: string
     *  - amount: number (can be negative; not 0)
     *  - posted_at?: datetime
     *  - remarks?: string
     */
    public function store(StudentBillingStoreRequest $request, TuitionService $tuition): JsonResponse
    {
        // StudentBillingStoreRequest->validated() already normalizes intStudentID and syid
        $data = $request->validated();
 
        // Actor id from middleware-injected faculty when available
        $faculty = $request->attributes->get('faculty');
        $actorId = $faculty ? (int) ($faculty->intID ?? null) : null;
 
        $item = $this->service->create($data, $actorId);

        // After adding a billing item, save tuition snapshot for this student+term.
        // Do not fail the request if snapshot save throws; keep response schema unchanged.
        try {
            $tuition->saveSnapshotByStudentId((int) $data['intStudentID'], (int) $data['syid'], $actorId);
        } catch (\Throwable $e) {
            // No-op: intentionally suppress to avoid blocking billing creation
        }
 
        return response()->json([
            'success' => true,
            'data'    => $item,
        ], 201);
    }

    /**
     * PUT /api/v1/finance/student-billing/{id}
     * Body (StudentBillingUpdateRequest):
     *  - description?: string
     *  - amount?: number (can be negative; not 0)
     *  - posted_at?: datetime
     *  - remarks?: string
     */
    public function update(int $id, StudentBillingUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Actor id from middleware-injected faculty when available
        $faculty = $request->attributes->get('faculty');
        $actorId = $faculty ? (int) ($faculty->intID ?? null) : null;

        $item = $this->service->update($id, $data, $actorId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Student billing item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * DELETE /api/v1/finance/student-billing/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $existing = $this->service->get($id);
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Student billing item not found',
            ], 404);
        }

        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Student billing item deleted',
        ]);
    }
}
