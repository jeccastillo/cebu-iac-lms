<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ClasslistMergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClasslistMergeController extends Controller
{
    /**
     * POST /api/v1/classlists/merge
     *
     * Body:
     * {
     *   "target_id": number,
     *   "source_ids": number[],
     *   "options": object (optional)
     * }
     *
     * Guards:
     * - Route-level middleware should restrict to role:registrar,admin.
     *
     * Behavior:
     * - Validates payload.
     * - Delegates to ClasslistMergeService.
     * - Returns summary result.
     */
    public function merge(Request $request): JsonResponse
    {
        $payload = $request->json()->all() ?: $request->all();
        $targetId = (int) ($payload['target_id'] ?? 0);
        $sourceIds = $payload['source_ids'] ?? null;

        if ($targetId <= 0 || !is_array($sourceIds) || count($sourceIds) < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload: target_id and source_ids[] are required',
            ], 422);
        }

        try {
            /** @var ClasslistMergeService $svc */
            $svc = app(ClasslistMergeService::class);
            $summary = $svc->merge($targetId, $sourceIds, $this->resolveActorId($request), $request);

            return response()->json([
                'success' => true,
                'result'  => $summary,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Merge failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve actor ID (if propagated via headers) for logging/auditing.
     * Falls back to null if not present.
     */
    protected function resolveActorId(Request $request): ?int
    {
        $fid = $request->header('X-Faculty-ID');
        if ($fid !== null && $fid !== '') {
            $n = (int) $fid;
            if ($n > 0) {
                return $n;
            }
        }
        return null;
    }
}
