<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PreviousSchoolStoreRequest;
use App\Http\Requests\Api\V1\PreviousSchoolUpdateRequest;
use App\Http\Resources\PreviousSchoolResource;
use App\Models\PreviousSchool;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreviousSchoolController extends Controller
{
    /**
     * GET /api/v1/previous-schools
     * GET /api/v1/admissions/previous-schools (public read-only)
     *
     * Query params (all optional):
     *  - search: string (applies to name/city/province/country LIKE %search%)
     *  - sort: name|city|province|country|grade|created_at (default: name)
     *  - order: asc|desc (default: asc)
     *  - page: int -> when present, returns paginated response
     *  - per_page: int (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $q = PreviousSchool::query();

        // Filters
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('city', 'like', $like)
                  ->orWhere('province', 'like', $like)
                  ->orWhere('country', 'like', $like);
            });
        }

        // Sorting
        $allowedSort = ['name', 'city', 'province', 'country', 'created_at'];
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
                'data' => PreviousSchoolResource::collection(collect($p->items())),
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
            'data' => PreviousSchoolResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/previous-schools/{id}
     */
    public function show(int $id): JsonResponse
    {
        $row = PreviousSchool::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Previous school not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PreviousSchoolResource($row),
        ]);
    }

    /**
     * POST /api/v1/previous-schools
     */
    public function store(PreviousSchoolStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $row = PreviousSchool::create($data);

        // System log: create
        SystemLogService::log(
            'create',
            'PreviousSchool',
            (int) ($row->intID ?? 0),
            null,
            $row,
            $request
        );

        return response()->json([
            'success' => true,
            'data' => new PreviousSchoolResource($row),
        ], 201);
    }

    /**
     * PUT /api/v1/previous-schools/{id}
     */
    public function update(PreviousSchoolUpdateRequest $request, int $id): JsonResponse
    {
        $row = PreviousSchool::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Previous school not found',
            ], 404);
        }

        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => true,
                'data' => new PreviousSchoolResource($row),
            ]);
        }

        $old = $row->replicate();

        $row->update($data);

        // System log: update
        SystemLogService::log(
            'update',
            'PreviousSchool',
            (int) ($row->intID ?? $id),
            $old,
            $row->fresh(),
            $request
        );

        return response()->json([
            'success' => true,
            'data' => new PreviousSchoolResource($row->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/previous-schools/{id}
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $row = PreviousSchool::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Previous school not found',
            ], 404);
        }

        $old = $row->replicate();
        $row->delete();

        // System log: delete
        SystemLogService::log(
            'delete',
            'PreviousSchool',
            (int) ($old->intID ?? $id),
            $old,
            null,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Previous school deleted',
        ]);
    }
}
