<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use App\Http\Requests\Api\V1\FacultyStoreRequest;
use App\Http\Requests\Api\V1\FacultyUpdateRequest;
use App\Http\Resources\FacultyResource;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
{
    /**
     * GET /api/v1/faculty
     * Query params:
     *  - q?: string (search by name)
     *  - teaching?: 0|1
     *  - isActive?: 0|1
     */
    public function index(Request $request): JsonResponse
    {
        $query = Faculty::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('strFirstname', 'like', $like)
                    ->orWhere('strLastname', 'like', $like)
                    ->orWhere('strMiddlename', 'like', $like)
                    ->orWhere('strUsername', 'like', $like);
            });
        }

        if ($request->filled('teaching')) {
            $t = (int) $request->query('teaching');
            if (in_array($t, [0, 1], true)) {
                $query->where('teaching', $t);
            }
        }

        if ($request->filled('isActive')) {
            $a = (int) $request->query('isActive');
            if (in_array($a, [0, 1], true)) {
                $query->where('isActive', $a);
            }
        }

        // Optional campus filter (used by Cashier assignment UI)
        if ($request->filled('campus_id')) {
            $query->where('campus_id', (int) $request->query('campus_id'));
        }

        // Pagination (defaults: page=1, per_page=20; cap per_page to 100)
        $per = (int) $request->query('per_page', 20);
        if ($per <= 0) $per = 20;
        if ($per > 100) $per = 100;

        $rows = $query->orderBy('strLastname')
            ->orderBy('strFirstname')
            ->paginate($per)
            ->appends($request->query());

        return response()->json([
            'success' => true,
            'data' => FacultyResource::collection($rows->items()),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
                'last_page'    => $rows->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/faculty/{id}
     */
    public function show(int $id): JsonResponse
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FacultyResource($faculty),
        ]);
    }

    /**
     * POST /api/v1/faculty
     */
    public function store(FacultyStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Normalize optional fields to satisfy legacy NOT NULL columns
        if (!array_key_exists('strMiddlename', $data) || $data['strMiddlename'] === null) {
            $data['strMiddlename'] = '';
        }

        // Hash password
        $data['strPass'] = password_hash($data['strPass'], PASSWORD_BCRYPT);

        $faculty = Faculty::create($data);

        // System log: create
        try {
            SystemLogService::log('create', 'Faculty', (int) $faculty->intID, null, $faculty->toArray(), $request);
        } catch (\Throwable $e) {
            // Best-effort logging; do not fail the request
        }

        return response()->json([
            'success' => true,
            'data' => new FacultyResource($faculty),
        ], 201);
    }

    /**
     * PUT /api/v1/faculty/{id}
     */
    public function update(FacultyUpdateRequest $request, int $id): JsonResponse
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        $old = $faculty->toArray();

        $data = $request->validated();

        // Normalize optional fields to satisfy legacy NOT NULL columns
        if (!array_key_exists('strMiddlename', $data) || $data['strMiddlename'] === null) {
            $data['strMiddlename'] = '';
        }

        if (array_key_exists('strPass', $data)) {
            if ($data['strPass'] !== null && $data['strPass'] !== '') {
                $data['strPass'] = password_hash($data['strPass'], PASSWORD_BCRYPT);
            } else {
                unset($data['strPass']);
            }
        }

        if (!empty($data)) {
            $faculty->update($data);
        }

        $new = $faculty->fresh();

        // System log: update
        try {
            SystemLogService::log('update', 'Faculty', (int) $faculty->intID, $old, $new->toArray(), $request);
        } catch (\Throwable $e) {
            // Best-effort logging; do not fail the request
        }

        return response()->json([
            'success' => true,
            'data' => new FacultyResource($new),
        ]);
    }

    /**
     * DELETE /api/v1/faculty/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        // Fail-safe: block deletion if faculty is assigned to any classlist
        try {
            $hasClasslists = DB::table('tb_mas_classlist')->where('intFacultyID', $faculty->intID)->exists();
        } catch (\Throwable $e) {
            // If query fails, be conservative and prevent deletion
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete faculty: validation check failed.',
                'error'   => 'unknown_state',
            ], 409);
        }
        if ($hasClasslists) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete faculty: one or more classlists reference this faculty.',
                'error'   => 'in_use',
            ], 409);
        }

        $old = $faculty->toArray();

        try {
            $faculty->delete();
        } catch (QueryException $e) {
            // Likely foreign key constraint or legacy linkage
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete faculty due to existing references.',
                'error'   => 'conflict',
            ], 409);
        }

        // System log: delete
        try {
            SystemLogService::log('delete', 'Faculty', (int) $faculty->intID, $old, null, request());
        } catch (\Throwable $e) {
            // Best-effort logging
        }

        return response()->json([
            'success' => true,
            'message' => 'Faculty deleted successfully',
        ]);
    }
}
