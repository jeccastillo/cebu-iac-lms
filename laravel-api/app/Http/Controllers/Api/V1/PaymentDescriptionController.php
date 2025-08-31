<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaymentDescriptionStoreRequest;
use App\Http\Requests\Api\V1\PaymentDescriptionUpdateRequest;
use App\Http\Resources\PaymentDescriptionResource;
use App\Models\PaymentDescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentDescriptionController extends Controller
{
    /**
     * GET /api/v1/payment-descriptions
     * Query params (all optional):
     *  - search: string (applies to name LIKE %search%)
     *  - sort: name (default: name)
     *  - order: asc|desc (default: asc)
     *  - page: int -> when present, returns paginated response
     *  - per_page: int (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $q = PaymentDescription::query();

        // Filters
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where('name', 'like', $like);
        }

        // Sorting
        $allowedSort = ['name', 'amount'];
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
                'data' => PaymentDescriptionResource::collection(collect($p->items())),
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
            'data' => PaymentDescriptionResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/payment-descriptions/{id}
     */
    public function show(int $id): JsonResponse
    {
        $row = PaymentDescription::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Payment description not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentDescriptionResource($row),
        ]);
    }

    /**
     * POST /api/v1/payment-descriptions
     */
    public function store(PaymentDescriptionStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $row = PaymentDescription::create($data);

        return response()->json([
            'success' => true,
            'data' => new PaymentDescriptionResource($row),
        ], 201);
    }

    /**
     * PUT /api/v1/payment-descriptions/{id}
     */
    public function update(PaymentDescriptionUpdateRequest $request, int $id): JsonResponse
    {
        $row = PaymentDescription::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Payment description not found',
            ], 404);
        }

        $data = $request->validated();
        if (!empty($data)) {
            $row->update($data);
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentDescriptionResource($row->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/payment-descriptions/{id}
     * Hard delete (no soft deletes)
     */
    public function destroy(int $id): JsonResponse
    {
        $row = PaymentDescription::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Payment description not found',
            ], 404);
        }

        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment description deleted',
        ]);
    }
}
