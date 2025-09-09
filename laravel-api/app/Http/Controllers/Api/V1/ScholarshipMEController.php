<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ScholarshipMEService;
use App\Services\ScholarshipService;

class ScholarshipMEController extends Controller
{
    protected ScholarshipMEService $me;
    protected ScholarshipService $scholarships;

    public function __construct(ScholarshipMEService $me, ScholarshipService $scholarships)
    {
        $this->me = $me;
        $this->scholarships = $scholarships;
    }

    /**
     * GET /api/v1/scholarships/{id}/me
     * List active mutual-exclusion pairs for a scholarship/discount id.
     */
    public function list(int $id): JsonResponse
    {
        // Ensure base scholarship exists (for 404 semantics)
        $exists = $this->scholarships->get($id);
        if ($exists === null) {
            return response()->json([
                'success' => false,
                'message' => 'Scholarship not found'
            ], 404);
        }

        $items = $this->me->list($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'items' => $items
            ]
        ]);
    }

    /**
     * POST /api/v1/scholarships/{id}/me
     * Body: { other_id: int }
     * Create or activate an ME pair for (id, other_id).
     */
    public function add(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'other_id' => 'required|integer|min:1'
        ]);

        try {
            $res = $this->me->add($id, (int) $payload['other_id']);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create mutual exclusion pair'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $res
        ], 201);
    }

    /**
     * DELETE /api/v1/scholarships/{id}/me/{otherId}
     * Inactivate (or delete) an ME pair (id, otherId).
     */
    public function delete(int $id, int $otherId): JsonResponse
    {
        $res = $this->me->delete($id, $otherId);

        if (!$res['deleted']) {
            return response()->json([
                'success' => false,
                'message' => 'Pair not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $res
        ]);
    }
}
