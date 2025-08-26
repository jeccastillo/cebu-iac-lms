<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Faculty;
use App\Services\SystemLogService;

class RoleController extends Controller
{
    /**
     * GET /api/v1/roles
     * Optional query: include_inactive=1
     */
    public function index(Request $request)
    {
        $includeInactive = (int) $request->query('include_inactive', 0) === 1;

        $q = Role::query();
        if (!$includeInactive) {
            $q->where('intActive', 1);
        }

        $roles = $q->orderBy('strCode')->get();

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }

    /**
     * POST /api/v1/roles
     * Body: { strCode, strName, strDescription?, intActive? }
     */
    public function store(Request $request)
    {
        $code = trim((string) $request->input('strCode', ''));
        $name = trim((string) $request->input('strName', ''));
        $desc = $request->input('strDescription');
        $active = (int) $request->input('intActive', 1);

        if ($code === '' || $name === '') {
            return response()->json([
                'success' => false,
                'message' => 'strCode and strName are required',
            ], 422);
        }

        $exists = Role::where('strCode', $code)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Role code already exists',
            ], 409);
        }

        $role = Role::create([
            'strCode' => $code,
            'strName' => $name,
            'strDescription' => $desc,
            'intActive' => $active ? 1 : 0,
        ]);

        // System log: create
        SystemLogService::log('create', 'Role', $role->getKey(), null, $role->toArray(), $request);

        return response()->json([
            'success' => true,
            'data'    => $role,
        ], 201);
    }

    /**
     * PUT /api/v1/roles/{id}
     * Body: { strCode?, strName?, strDescription?, intActive? }
     */
    public function update(Request $request, int $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }

        // Snapshot original values before update
        $old = $role->toArray();

        $data = [];
        if ($request->has('strCode')) {
            $newCode = trim((string) $request->input('strCode', ''));
            if ($newCode === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'strCode cannot be empty',
                ], 422);
            }
            $dupe = Role::where('strCode', $newCode)->where('intRoleID', '!=', $id)->exists();
            if ($dupe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role code already exists',
                ], 409);
            }
            $data['strCode'] = $newCode;
        }
        if ($request->has('strName')) {
            $newName = trim((string) $request->input('strName', ''));
            if ($newName === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'strName cannot be empty',
                ], 422);
            }
            $data['strName'] = $newName;
        }
        if ($request->has('strDescription')) {
            $data['strDescription'] = $request->input('strDescription');
        }
        if ($request->has('intActive')) {
            $data['intActive'] = (int) $request->input('intActive') ? 1 : 0;
        }

        if (!empty($data)) {
            $role->update($data);

            // Snapshot new values after update
            $new = $role->fresh()->toArray();

            // System log: update
            SystemLogService::log('update', 'Role', $role->getKey(), $old, $new, $request);
        }

        return response()->json([
            'success' => true,
            'data'    => $role->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/roles/{id}
     * Soft-disable by setting intActive=0
     */
    public function destroy(int $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
            ], 404);
        }

        // Snapshot original values before soft-disable
        $old = $role->toArray();

        $role->update(['intActive' => 0]);

        // Snapshot new values after soft-disable
        $new = $role->fresh()->toArray();

        // System log: update (soft-disable)
        SystemLogService::log('update', 'Role', $role->getKey(), $old, $new, request());

        return response()->json([
            'success' => true,
            'message' => 'Role disabled',
        ]);
    }

    /**
     * GET /api/v1/faculty/{id}/roles
     * Returns { roles: [Role], codes: [string] }
     */
    public function facultyRoles(int $id)
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        $roles = $faculty->roles()->orderBy('strCode')->get();
        $codes = $roles->pluck('strCode')->toArray();
        $roleIds = $roles->pluck('intRoleID')->toArray();        

        return response()->json([
            'success' => true,
            'data'    => [
                'roles' => $roles,
                'codes' => $codes,
            ],
        ]);
    }

    /**
     * POST /api/v1/faculty/{id}/roles
     * Body: { role_ids?: number[], role_codes?: string[] }
     * Attaches any missing roles; keeps existing ones (no detach).
     */
    public function assignFacultyRoles(Request $request, int $id)
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        // Snapshot current roles before assignment
        $beforeRoles = $faculty->roles()->orderBy('strCode')->get();
        $old = [
            'role_ids' => $beforeRoles->pluck('intRoleID')->toArray(),
            'codes'    => $beforeRoles->pluck('strCode')->toArray(),
        ];

        $roleIds = $request->input('role_ids', []);
        $roleCodes = $request->input('role_codes', []);

        $ids = [];
        if (is_array($roleIds) && count($roleIds)) {
            $ids = array_values(array_unique(array_map('intval', $roleIds)));
        }

        if (is_array($roleCodes) && count($roleCodes)) {
            $codes = array_values(array_unique(array_filter(array_map(function ($c) {
                return strtolower(trim((string) $c));
            }, $roleCodes))));
            if (count($codes)) {
                $found = Role::whereIn('strCode', $codes)->pluck('intRoleID')->toArray();
                $ids = array_values(array_unique(array_merge($ids, $found)));
            }
        }

        if (!count($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid role identifiers provided',
            ], 422);
        }

        // Only attach roles that are active
        $validActive = Role::whereIn('intRoleID', $ids)->where('intActive', 1)->pluck('intRoleID')->toArray();
        if (!count($validActive)) {
            return response()->json([
                'success' => false,
                'message' => 'No active roles found to assign',
            ], 422);
        }

        $faculty->roles()->syncWithoutDetaching($validActive);

        $roles = $faculty->roles()->orderBy('strCode')->get();

        // System log: update faculty role assignments
        SystemLogService::log('update', 'FacultyRoles', $faculty->getKey(), $old, [
            'role_ids' => $roles->pluck('intRoleID')->toArray(),
            'codes'    => $roles->pluck('strCode')->toArray(),
        ], request());

        return response()->json([
            'success' => true,
            'data'    => [
                'roles' => $roles,
                'codes' => $roles->pluck('strCode')->toArray(),
            ],
        ]);
    }

    /**
     * DELETE /api/v1/faculty/{id}/roles/{roleId}
     */
    public function removeFacultyRole(int $id, int $roleId)
    {
        $faculty = Faculty::find($id);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found',
            ], 404);
        }

        // Snapshot current roles before removal
        $beforeRoles = $faculty->roles()->orderBy('strCode')->get();
        $old = [
            'role_ids' => $beforeRoles->pluck('intRoleID')->toArray(),
            'codes'    => $beforeRoles->pluck('strCode')->toArray(),
        ];

        $faculty->roles()->detach($roleId);

        $roles = $faculty->roles()->orderBy('strCode')->get();

        // System log: update faculty role removal
        SystemLogService::log('update', 'FacultyRoles', $faculty->getKey(), $old, [
            'role_ids' => $roles->pluck('intRoleID')->toArray(),
            'codes'    => $roles->pluck('strCode')->toArray(),
        ], request());

        return response()->json([
            'success' => true,
            'data'    => [
                'roles' => $roles,
                'codes' => $roles->pluck('strCode')->toArray(),
            ],
        ]);
    }
}
