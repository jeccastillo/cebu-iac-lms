<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApplicantTypeStoreRequest;
use App\Http\Requests\Api\V1\ApplicantTypeUpdateRequest;
use App\Http\Resources\ApplicantTypeResource;
use App\Models\ApplicantType;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicantTypeController extends Controller
{
    /**
     * GET /api/v1/applicant-types
     *
     * Query params (all optional):
     *  - search: string (applies to name LIKE %search%)
     *  - type: college|shs|grad (filter)
     *  - sort: name|type|created_at (default: name)
     *  - order: asc|desc (default: asc)
     *  - page: int -> when present, returns paginated response
     *  - per_page: int (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $q = ApplicantType::query();

        // Filters
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('name', 'like', $like);
            });
        }

        $type = (string) $request->query('type', '');
        if ($type !== '') {
            $q->where('type', $type);
        }

        // Sorting
        $allowedSort = ['name', 'type', 'created_at'];
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
                'data' => ApplicantTypeResource::collection(collect($p->items())),
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
            'data' => ApplicantTypeResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/applicant-types/{id}
     */
    public function show(int $id): JsonResponse
    {
        $row = ApplicantType::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant type not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ApplicantTypeResource($row),
        ]);
    }

    /**
     * POST /api/v1/applicant-types
     */
    public function store(ApplicantTypeStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $row = ApplicantType::create($data);

        // System log: create
        SystemLogService::log(
            'create',
            'ApplicantType',
            (int) ($row->intID ?? 0),
            null,
            $row,
            $request
        );

        return response()->json([
            'success' => true,
            'data' => new ApplicantTypeResource($row),
        ], 201);
    }

    /**
     * PUT /api/v1/applicant-types/{id}
     */
    public function update(ApplicantTypeUpdateRequest $request, int $id): JsonResponse
    {
        $row = ApplicantType::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant type not found',
            ], 404);
        }

        $data = $request->validated();

        if (empty($data)) {
            return response()->json([
                'success' => true,
                'data' => new ApplicantTypeResource($row),
            ]);
        }

        $old = $row->replicate();

        // If unique(name,type) validation depends on type when not provided,
        // ensure we keep current type for composite uniqueness check at DB level.
        if (!array_key_exists('type', $data)) {
            $data['type'] = $row->type;
        }

        $row->update($data);

        // System log: update
        SystemLogService::log(
            'update',
            'ApplicantType',
            (int) ($row->intID ?? $id),
            $old,
            $row->fresh(),
            $request
        );

        return response()->json([
            'success' => true,
            'data' => new ApplicantTypeResource($row->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/applicant-types/{id}
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $row = ApplicantType::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant type not found',
            ], 404);
        }

        $old = $row->replicate();
        $row->delete();

        // System log: delete
        SystemLogService::log(
            'delete',
            'ApplicantType',
            (int) ($old->intID ?? $id),
            $old,
            null,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Applicant type deleted',
        ]);
    }
}
