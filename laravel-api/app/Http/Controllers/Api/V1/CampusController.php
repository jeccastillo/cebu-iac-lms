<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use Illuminate\Http\Request;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CampusController extends Controller
{
    /**
     * GET /api/v1/campuses
     * Optional query params:
     *  - q: search term (matches campus_name or description, case-insensitive)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $query = Campus::query();

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('campus_name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $data = $query->orderBy('campus_name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/campuses/{id}
     */
    public function show(int $id)
    {
        $campus = Campus::find($id);
        if (!$campus) {
            return response()->json([
                'success' => false,
                'message' => 'Campus not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $campus,
        ]);
    }

    /**
     * POST /api/v1/campuses
     * Body: { campus_name: string (required, unique), description?: string }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'campus_name' => 'required|string|max:255|unique:tb_mas_campuses,campus_name',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (!array_key_exists('status', $validated) || $validated['status'] === null) {
            $validated['status'] = 'active';
        }

        $campus = Campus::create($validated);

        // System log: create
        SystemLogService::log('create', 'Campus', $campus->id, null, $campus->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $campus,
        ], 201);
    }

    /**
     * PUT /api/v1/campuses/{id}
     * Body: { campus_name?: string (unique), description?: string }
     */
    public function update(Request $request, int $id)
    {
        $campus = Campus::find($id);
        if (!$campus) {
            return response()->json([
                'success' => false,
                'message' => 'Campus not found',
            ], 404);
        }

        $validated = $request->validate([
            'campus_name' => 'sometimes|required|string|max:255|unique:tb_mas_campuses,campus_name,' . $id . ',id',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        // Snapshot original values before update
        $old = $campus->toArray();

        $campus->fill($validated);
        $campus->save();

        // Snapshot new values after update
        $new = $campus->fresh()->toArray();

        // System log: update
        SystemLogService::log('update', 'Campus', $campus->id, $old, $new, $request);

        return response()->json([
            'success' => true,
            'data' => $campus,
        ]);
    }

    /**
     * DELETE /api/v1/campuses/{id}
     */
    public function destroy(int $id)
    {
        $campus = Campus::find($id);
        if (!$campus) {
            return response()->json([
                'success' => false,
                'message' => 'Campus not found',
            ], 404);
        }

        // Prevent deletion if campus is referenced by other tables
        $tablesToCheck = [
            'tb_mas_users',
            'tb_mas_faculty',
            'tb_mas_programs',
            'tb_mas_curriculum',
            'tb_mas_classrooms',
            'tb_mas_classlist',
            'tb_mas_subjects',
            'tb_mas_students',
            'tb_mas_sy',
        ];

        $usage = [];
        foreach ($tablesToCheck as $tableName) {
            try {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'campus_id')) {
                    $count = DB::table($tableName)->where('campus_id', $id)->count();
                    if ($count > 0) {
                        $usage[$tableName] = $count;
                    }
                }
            } catch (\Throwable $e) {
                // ignore table/column engine issues and continue
            }
        }

        if (!empty($usage)) {
            return response()->json([
                'success' => false,
                'message' => 'Campus cannot be deleted because it is referenced by other data.',
                'usage' => $usage,
                'suggestion' => 'Set status to inactive instead of deleting.',
            ], 409);
        }

        // Snapshot original values before delete
        $old = $campus->toArray();

        try {
            $campus->delete();
        } catch (\Throwable $e) {
            // If FK constraints prevent delete (should be nullOnDelete, but guard anyway)
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete campus: ' . $e->getMessage(),
            ], 409);
        }

        // System log: delete
        SystemLogService::log('delete', 'Campus', $id, $old, null, request());

        return response()->json([
            'success' => true,
            'data' => null,
        ]);
    }
}
