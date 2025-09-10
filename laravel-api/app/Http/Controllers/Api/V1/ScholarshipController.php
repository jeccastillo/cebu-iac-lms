<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ScholarshipService;
use App\Http\Requests\Api\V1\ScholarshipStoreRequest;
use App\Http\Requests\Api\V1\ScholarshipUpdateRequest;
use App\Http\Requests\Api\V1\ScholarshipAssignmentStoreRequest;
use App\Http\Requests\Api\V1\ScholarshipAssignmentApplyRequest;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * GET /api/v1/scholarships/{id}
     * Show one scholarship by ID.
     */
    public function show(int $id): JsonResponse
    {
        $row = $this->scholarships->get($id);
        if ($row === null) {
            return response()->json([
                'success' => false,
                'message' => 'Scholarship not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => new ScholarshipResource($row),
        ]);
    }

    /**
     * POST /api/v1/scholarships
     * Create a new scholarship/discount catalog row.
     */
    public function store(ScholarshipStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $row = $this->scholarships->create($data);
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            $isDuplicate = $e->getCode() === '23000'
                || stripos($msg, 'Duplicate entry') !== false
                || stripos($msg, 'unique') !== false;
            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate code or name.',
                    'errors'  => [
                        'unique' => ['Code and Name must be unique.']
                    ],
                ], 422);
            }
            throw $e;
        }
        return response()->json([
            'success' => true,
            'data'    => new ScholarshipResource($row),
        ], 201);
    }

    /**
     * PUT /api/v1/scholarships/{id}
     * Update an existing scholarship/discount catalog row.
     */
    public function update(ScholarshipUpdateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        try {
            $row = $this->scholarships->update($id, $data);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scholarship not found',
            ], 404);
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            $isDuplicate = $e->getCode() === '23000'
                || stripos($msg, 'Duplicate entry') !== false
                || stripos($msg, 'unique') !== false;
            if ($isDuplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate code or name.',
                    'errors'  => [
                        'unique' => ['Code and Name must be unique.']
                    ],
                ], 422);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'data'    => new ScholarshipResource($row),
        ]);
    }

    /**
     * POST /api/v1/scholarships/upsert (stub)
     * DELETE /api/v1/scholarships/{id} (soft delete implemented)
     * For now, upsert remains not implemented for assignment parity.
     */
    public function upsert(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Not Implemented'
        ], 501);
    }

    /**
     * DELETE /api/v1/scholarships/{id}
     * Soft delete: set status=inactive (idempotent). If no status column, hard delete fallback.
     */
    public function delete(int $id): JsonResponse
    {
        try {
            $row = $this->scholarships->softDelete($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scholarship not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Scholarship disabled',
            'data'    => new ScholarshipResource($row),
        ]);
    }

    /**
     * POST /api/v1/scholarships/{id}/restore
     * Restore a soft-deleted scholarship (status=active).
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $row = $this->scholarships->restore($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scholarship not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Scholarship restored',
            'data'    => new ScholarshipResource($row),
        ]);
    }

    /**
     * GET /api/v1/scholarships/assignments
     * List assignment rows for a term; optionally filter by student_id or search query.
     */
    public function assignments(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'syid'       => 'required|integer',
            'student_id' => 'sometimes|integer',
            'q'          => 'sometimes|string',
        ]);

        $items = $this->scholarships->listAssignments($payload);

        return response()->json([
            'success' => true,
            'data'    => [
                'items' => $items,
            ],
        ]);
    }

    /**
     * POST /api/v1/scholarships/assignments
     * Create a pending assignment for (student_id, syid, discount_id) if not existing.
     */
    public function assignmentsStore(ScholarshipAssignmentStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $row = $this->scholarships->assignmentUpsert($data);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment',
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error creating assignment',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $row,
        ], 201);
    }

    /**
     * PATCH /api/v1/scholarships/assignments/apply
     * Bulk-apply pending assignments by IDs.
     */
    public function assignmentsApply(ScholarshipAssignmentApplyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ids = $validated['ids'] ?? [];
        $force = isset($validated['force']) ? (bool) $validated['force'] : (bool) $request->input('force', false);
        $actorId = optional($request->user())->intID ?? null;

        $res = $this->scholarships->applyAssignments($ids, $actorId, $force);

        return response()->json([
            'success' => true,
            'data'    => $res,
        ]);
    }

    /**
     * DELETE /api/v1/scholarships/assignments/{id}
     * Delete an assignment when not applied.
     */
    public function assignmentsDelete(Request $request, int $id): JsonResponse
    {
        try {
            $actorId = optional($request->user())->intID ?? null;
            $res = $this->scholarships->deleteAssignment($id, $actorId);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if (!$res['deleted']) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $res,
        ]);
    }
}
