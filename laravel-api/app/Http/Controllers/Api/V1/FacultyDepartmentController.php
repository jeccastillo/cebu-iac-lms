<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DepartmentContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultyDepartmentController extends Controller
{
    /**
     * GET /api/v1/faculty/{id}/departments
     * List department tags for a faculty.
     */
    public function index(int $id, Request $request, DepartmentContextService $ctx): JsonResponse
    {
        // Soft check that faculty exists
        $exists = DB::table('tb_mas_faculty')->where('intID', $id)->exists();
        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        $rows = DB::table('tb_mas_faculty_departments')
            ->where('intFacultyID', $id)
            ->orderBy('department_code')
            ->orderBy('campus_id')
            ->get(['department_code', 'campus_id']);

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    /**
     * POST /api/v1/faculty/{id}/departments
     * Body: { department_code: string, campus_id?: int|null }
     * Create (first-or-create) a department tag for a faculty.
     */
    public function store(int $id, Request $request, DepartmentContextService $ctx): JsonResponse
    {
        // Soft check that faculty exists
        $exists = DB::table('tb_mas_faculty')->where('intID', $id)->exists();
        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        $payload = $request->validate([
            'department_code' => 'required|string',
            'campus_id'       => 'nullable|integer',
        ]);

        $code = strtolower(trim((string) $payload['department_code']));
        $valid = $ctx->departmentCodes();
        if (!in_array($code, $valid, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid department_code',
                'errors'  => [ 'department_code' => ['Invalid department code'] ],
            ], 422);
        }

        $campusId = array_key_exists('campus_id', $payload)
            ? ($payload['campus_id'] !== null ? (int) $payload['campus_id'] : null)
            : null;

        // Upsert (firstOrCreate semantics)
        $existing = DB::table('tb_mas_faculty_departments')
            ->where('intFacultyID', $id)
            ->whereRaw('LOWER(department_code) = ?', [$code])
            ->when($campusId === null, function ($q) {
                $q->whereNull('campus_id');
            }, function ($q) use ($campusId) {
                $q->where('campus_id', $campusId);
            })
            ->first();

        if (!$existing) {
            DB::table('tb_mas_faculty_departments')->insert([
                'intFacultyID'    => $id,
                'department_code' => $code,
                'campus_id'       => $campusId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } else {
            // Touch updated_at
            DB::table('tb_mas_faculty_departments')
                ->where('intFacultyID', $id)
                ->whereRaw('LOWER(department_code) = ?', [$code])
                ->when($campusId === null, function ($q) {
                    $q->whereNull('campus_id');
                }, function ($q) use ($campusId) {
                    $q->where('campus_id', $campusId);
                })
                ->update(['updated_at' => now()]);
        }

        $row = [
            'department_code' => $code,
            'campus_id'       => $campusId,
        ];

        return response()->json([
            'success' => true,
            'data'    => $row,
            'message' => 'Tag saved',
        ], 201);
    }

    /**
     * DELETE /api/v1/faculty/{id}/departments/{code}?campus_id=...
     * Delete a department tag mapping for a faculty (composite key by code + campus_id).
     */
    public function destroy(int $id, string $code, Request $request): JsonResponse
    {
        // Soft check that faculty exists
        $exists = DB::table('tb_mas_faculty')->where('intID', $id)->exists();
        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        $code = strtolower(trim((string) $code));
        $campusIdParam = $request->query('campus_id', null);
        $campusId = ($campusIdParam !== null && $campusIdParam !== '' && is_numeric($campusIdParam))
            ? (int) $campusIdParam
            : null;

        $q = DB::table('tb_mas_faculty_departments')
            ->where('intFacultyID', $id)
            ->whereRaw('LOWER(department_code) = ?', [$code]);

        if ($campusId === null) {
            $q->whereNull('campus_id');
        } else {
            $q->where('campus_id', $campusId);
        }

        $existing = $q->first();
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        $q->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag removed',
        ]);
    }
}
