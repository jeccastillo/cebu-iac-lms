<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaymentModeStoreRequest;
use App\Http\Requests\Api\V1\PaymentModeUpdateRequest;
use App\Http\Resources\PaymentModeResource;
use App\Models\PaymentMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentModeController extends Controller
{
    /**
     * GET /api/v1/payment-modes
     * Query params (all optional):
     *  - search: string (applies to name LIKE %search%)
     *  - type: string
     *  - is_active: 0|1|true|false
     *  - is_nonbank: 0|1|true|false
     *  - pchannel: string
     *  - pmethod: string
     *  - sort: name|type|pchannel|pmethod|charge (default: name)
     *  - order: asc|desc (default: asc)
     *  - page: int -> when present, returns paginated response
     *  - per_page: int (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $q = PaymentMode::query();

        // Filters
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('name', 'like', $like);
            });
        }
        if ($request->filled('type')) {
            $q->where('type', (string) $request->query('type'));
        }
        if ($request->filled('is_active')) {
            $q->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('is_nonbank')) {
            $q->where('is_nonbank', filter_var($request->query('is_nonbank'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('pchannel')) {
            $q->where('pchannel', (string) $request->query('pchannel'));
        }
        if ($request->filled('pmethod')) {
            $q->where('pmethod', (string) $request->query('pmethod'));
        }

        // Sorting
        $allowedSort = ['name', 'type', 'pchannel', 'pmethod', 'charge'];
        $sort = $request->query('sort', 'name');
        $order = strtolower((string) $request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'name';
        }

        // Pagination optional
        $paginate = $request->filled('page') || $request->filled('per_page');
        if ($paginate) {
            $perPage = max(1, (int) $request->query('per_page', 20));
            $p = $q->orderBy($sort, $order)->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => PaymentModeResource::collection(collect($p->items())),
                'meta' => [
                    'current_page' => $p->currentPage(),
                    'per_page' => $p->perPage(),
                    'total' => $p->total(),
                    'last_page' => $p->lastPage(),
                ],
            ]);
        }

        $rows = $q->orderBy($sort, $order)->get();
        return response()->json([
            'success' => true,
            'data' => PaymentModeResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/payment-modes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $mode = PaymentMode::find($id);
        if (!$mode) {
            return response()->json([
                'success' => false,
                'message' => 'Payment mode not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentModeResource($mode),
        ]);
    }

    /**
     * POST /api/v1/payment-modes
     */
    public function store(PaymentModeStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Defaults
        if (!array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }
        if (!array_key_exists('is_nonbank', $data)) {
            $data['is_nonbank'] = false;
        }
        if (!array_key_exists('charge', $data)) {
            $data['charge'] = 0;
        }

        $mode = PaymentMode::create($data);

        return response()->json([
            'success' => true,
            'data' => new PaymentModeResource($mode),
        ], 201);
    }

    /**
     * PUT /api/v1/payment-modes/{id}
     */
    public function update(PaymentModeUpdateRequest $request, int $id): JsonResponse
    {
        $mode = PaymentMode::find($id);
        if (!$mode) {
            return response()->json([
                'success' => false,
                'message' => 'Payment mode not found',
            ], 404);
        }

        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => true,
                'data' => new PaymentModeResource($mode),
            ]);
        }

        $mode->update($data);

        return response()->json([
            'success' => true,
            'data' => new PaymentModeResource($mode->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/payment-modes/{id}
     * Soft delete
     */
    public function destroy(int $id): JsonResponse
    {
        $mode = PaymentMode::find($id);
        if (!$mode) {
            return response()->json([
                'success' => false,
                'message' => 'Payment mode not found',
            ], 404);
        }

        $mode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment mode deleted',
        ]);
    }

    /**
     * POST /api/v1/payment-modes/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        $mode = PaymentMode::withTrashed()->find($id);
        if (!$mode) {
            return response()->json([
                'success' => false,
                'message' => 'Payment mode not found',
            ], 404);
        }

        if ($mode->trashed()) {
            $mode->restore();
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentModeResource($mode->fresh()),
        ]);
    }
}
