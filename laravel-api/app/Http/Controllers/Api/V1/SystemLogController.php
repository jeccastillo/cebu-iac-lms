<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    /**
     * GET /api/v1/system-logs
     *
     * Query params (all optional):
     *  - page: int (default 1)
     *  - per_page: int (default 20, max 100)
     *  - entity: string
     *  - action: string
     *  - user_id: int
     *  - entity_id: int
     *  - method: string
     *  - path: string (exact or partial match via q)
     *  - q: string (search in entity, action, path, method, user_agent)
     *  - date_from: date (YYYY-MM-DD)
     *  - date_to: date (YYYY-MM-DD)
     *
     * Returns:
     * {
     *   success: true,
     *   data: [ { SystemLog }, ... ],
     *   meta: { current_page, per_page, total, last_page }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'page'       => 'sometimes|integer|min:1',
            'per_page'   => 'sometimes|integer|min:1|max:100',
            'entity'     => 'sometimes|string',
            'action'     => 'sometimes|string',
            'user_id'    => 'sometimes|integer',
            'entity_id'  => 'sometimes|integer',
            'method'     => 'sometimes|string',
            'path'       => 'sometimes|string',
            'q'          => 'sometimes|string',
            'date_from'  => 'sometimes|date',
            'date_to'    => 'sometimes|date',
        ]);

        $query = SystemLog::query();

        if (isset($payload['entity']) && $payload['entity'] !== '') {
            $query->where('entity', $payload['entity']);
        }
        if (isset($payload['action']) && $payload['action'] !== '') {
            $query->where('action', $payload['action']);
        }
        if (isset($payload['user_id'])) {
            $query->where('user_id', (int)$payload['user_id']);
        }
        if (isset($payload['entity_id'])) {
            $query->where('entity_id', (int)$payload['entity_id']);
        }
        if (isset($payload['method']) && $payload['method'] !== '') {
            $query->where('method', $payload['method']);
        }
        if (isset($payload['path']) && $payload['path'] !== '') {
            $query->where('path', $payload['path']);
        }

        if (isset($payload['q']) && trim($payload['q']) !== '') {
            $q = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($payload['q'])) . '%';
            $query->where(function ($w) use ($q) {
                $w->where('entity', 'like', $q)
                  ->orWhere('action', 'like', $q)
                  ->orWhere('path', 'like', $q)
                  ->orWhere('method', 'like', $q)
                  ->orWhere('user_agent', 'like', $q);
            });
        }

        if (isset($payload['date_from'])) {
            $query->where('created_at', '>=', $payload['date_from'] . ' 00:00:00');
        }
        if (isset($payload['date_to'])) {
            $query->where('created_at', '<=', $payload['date_to'] . ' 23:59:59');
        }

        $query->orderBy('created_at', 'desc');

        $perPage = isset($payload['per_page']) ? (int)$payload['per_page'] : 20;
        if ($perPage < 1) $perPage = 20;
        if ($perPage > 100) $perPage = 100;
        $page = isset($payload['page']) ? (int)$payload['page'] : 1;

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }
}
