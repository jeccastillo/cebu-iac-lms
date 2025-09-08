<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClassroomUpsertRequest;
use App\Http\Resources\ClassroomResource;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    /**
     * GET /api/v1/classrooms
     * Lists all classrooms with basic information
     * Query params:
     *  - search: filters by classroom name (LIKE %term%)
     *  - campus_id: optional filter by campus_id
     *  - limit, page: simple pagination (default 25 per page)
     */

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $classroomId = $request->query('classroom_id');
        $campusId = $request->query('campus_id');
        $limit = (int) ($request->query('limit', 25));
        $page = max(1, (int) ($request->query('page', 1)));
        $offset = ($page - 1) * $limit;

        $q = DB::table('tb_mas_classrooms')
            ->leftJoin('tb_mas_campuses', 'tb_mas_campuses.id', '=', 'tb_mas_classrooms.campus_id');

        if ($classroomId) {
            $q->where('tb_mas_classrooms.intID', $classroomId);
        }

        if ($campusId !== null && $campusId !== '') {
            $q->where('tb_mas_classrooms.campus_id', (int) $campusId);
        }

        if ($search !== '') {
            $q->where(function($query) use ($search) {
                $query->where('tb_mas_classrooms.strRoomCode', 'LIKE', "%{$search}%")
                    ->orWhere('tb_mas_classrooms.description', 'LIKE', "%{$search}%")
                    ->orWhere('tb_mas_campuses.campus_name', 'LIKE', "%{$search}%");
            });
        }

        $total = $q->count();

        $items = $q->orderBy('tb_mas_classrooms.intID', 'asc')
            ->offset($offset)
            ->limit($limit)
            ->select([
                'tb_mas_classrooms.intID',
                'tb_mas_classrooms.enumType',
                'tb_mas_classrooms.strRoomCode',
                'tb_mas_classrooms.description',
                'tb_mas_campuses.campus_name'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => ClassroomResource::collection($items),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * POST /api/v1/curriculum
     * Creates a new curriculum
     */
    public function store(ClassroomUpsertRequest $request)
    {
        $data = $request->validated();

        $newId = DB::table('tb_mas_classrooms')->insertGetId($data);
        $created = DB::table('tb_mas_classrooms')->where('intID', $newId)->first();

        // System log: create
        SystemLogService::log('create', 'Classroom', (int) $newId, null, $created, $request);

        return response()->json([
            'success' => true,
            'message' => 'Classroom created successfully',
            'newid' => (int) $newId,
            'data' => new ClassroomResource($created),
        ], 201);
    }

    /**
     * GET /api/v1/classroom/{id}
     * Returns a specific classroom with its details
     */
    public function show($id)
    {
        $classroom = DB::table('tb_mas_classrooms')
            ->leftJoin('tb_mas_campuses', 'tb_mas_campuses.id', '=', 'tb_mas_classrooms.campus_id')
            ->where('tb_mas_classrooms.intID', $id)
            ->select([
                'tb_mas_classrooms.intID',
                'tb_mas_classrooms.enumType',
                'tb_mas_classrooms.strRoomCode',
                'tb_mas_classrooms.description',
                'tb_mas_classrooms.campus_id',
                'tb_mas_campuses.campus_name'
            ])
            ->first();

        if (!$classroom) {
            return response()->json([
                'success' => false,
                'message' => 'Classroom not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ClassroomResource($classroom)
        ]);
    }

    /**
     * PUT /api/v1/classroom/{id}
     * Updates an existing classroom
     */
    public function update(ClassroomUpsertRequest $request, $id)
    {
        $classroom = DB::table('tb_mas_classrooms')->where('intID', $id)->first();

        if (!$classroom) {
            return response()->json([
                'success' => false,
                'message' => 'Classroom not found'
            ], 404);
        }

        $data = $request->validated();

        // Capture old values for logging
        $old = $classroom;

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields to update'
            ], 422);
        }

        DB::table('tb_mas_classrooms')->where('intID', $id)->update($data);

        $updated = DB::table('tb_mas_classrooms')->where('intID', $id)->first();

        // System log: update
        SystemLogService::log('update', 'Classroom', (int) $id, $old, $updated, $request);

        return response()->json([
            'success' => true,
            'message' => 'Classroom updated successfully',
            'data' => new ClassroomResource($updated),
        ]);
    }

    /**
     * DELETE /api/v1/classroom/{id}
     * Deletes a classroom
     */
    public function destroy($id)
    {
        $classroom = DB::table('tb_mas_classrooms')->where('intID', $id)->first();

        if (!$classroom) {
            return response()->json([
                'success' => false,
                'message' => 'Classroom not found'
            ], 404);
        }

        DB::table('tb_mas_classrooms')->where('intID', $id)->delete();

        // System log: delete
        SystemLogService::log('delete', 'Classroom', (int) $id, $classroom, null, request());

        return response()->json([
            'success' => true,
            'message' => 'Classroom deleted successfully'
        ]);
    }
}
