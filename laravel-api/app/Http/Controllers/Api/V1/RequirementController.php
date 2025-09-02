<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RequirementStoreRequest;
use App\Http\Requests\Api\V1\RequirementUpdateRequest;
use App\Http\Resources\RequirementResource;
use App\Models\Requirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequirementController extends Controller
{
    /**
     * GET /api/v1/requirements
     * Query params (all optional):
     *  - search: string (applies to name LIKE %search%)
     *  - type: college|shs|grad
     *  - is_foreign: 0|1|true|false
     *  - sort: name|type|is_foreign|created_at (default: name)
     *  - order: asc|desc (default: asc)
     *  - page: int -> when present, returns paginated response
     *  - per_page: int (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $q = Requirement::query();

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
        if ($request->filled('is_foreign')) {
            $q->where('is_foreign', filter_var($request->query('is_foreign'), FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $allowedSort = ['name', 'type', 'is_foreign', 'created_at'];
        $sort = (string) $request->query('sort', 'name');
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
                'data' => RequirementResource::collection(collect($p->items())),
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
            'data' => RequirementResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/requirements/{id}
     */
    public function show(int $id): JsonResponse
    {
        $row = Requirement::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RequirementResource($row),
        ]);
    }

    /**
     * POST /api/v1/requirements
     */
    public function store(RequirementStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!array_key_exists('is_foreign', $data)) {
            $data['is_foreign'] = false;
        }
        if (!array_key_exists('is_initial_requirements', $data)) {
            $data['is_initial_requirements'] = false;
        }

        $row = Requirement::create($data);

        return response()->json([
            'success' => true,
            'data' => new RequirementResource($row),
        ], 201);
    }

    /**
     * PUT /api/v1/requirements/{id}
     */
    public function update(RequirementUpdateRequest $request, int $id): JsonResponse
    {
        $row = Requirement::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found',
            ], 404);
        }

        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => true,
                'data' => new RequirementResource($row),
            ]);
        }

        $row->update($data);

        return response()->json([
            'success' => true,
            'data' => new RequirementResource($row->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/requirements/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $row = Requirement::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Requirement not found',
            ], 404);
        }

        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Requirement deleted',
        ]);
    }
}
