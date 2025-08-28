<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClasslistFinalizeRequest;
use App\Http\Requests\Api\V1\ClasslistGradeSaveRequest;
use App\Services\ClasslistService;
use App\Services\GradingWindowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ClasslistGradesController extends Controller
{
    protected ClasslistService $classlists;
    protected GradingWindowService $windows;

    public function __construct(ClasslistService $classlists, GradingWindowService $windows)
    {
        $this->classlists = $classlists;
        $this->windows = $windows;
    }

    /**
     * GET /api/v1/classlists/{id}/viewer
     * Returns classlist, students, grade options (midterm/finals), window info, and permissions.
     */
    public function viewerData(Request $request, int $id): JsonResponse
    {
        $cl = $this->classlists->getClasslistForGrading($id);
        if (!$cl) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        $user = $request->user();
        $role = $this->resolveUserPrimaryRole($user);
        $assignedFaculty = $this->isAssignedFaculty($user, $cl);

        // Prefer Gate-based permissions (works with CodeIgniterSessionGuard and Laravel Auth),
        // fallback to role heuristics if Gate has no context/user.
        $canEditGate       = Gate::allows('grade.classlist.edit', $id);
        $canFinalizeGate   = Gate::allows('grade.classlist.finalize', $id);
        $canUnfinalizeGate = Gate::allows('grade.classlist.unfinalize', $id);

        // Header fallbacks from SPA for hybrid environments
        $headerRoles = [];
        try {
            $hdr = (string) ($request->header('X-User-Roles') ?? '');
            if ($hdr !== '') {
                $headerRoles = array_filter(array_map('trim', explode(',', strtolower($hdr))));
            }
        } catch (\Throwable $e) {
            $headerRoles = [];
        }
        $headerIsAdminOrRegistrar = in_array('admin', $headerRoles, true) || in_array('registrar', $headerRoles, true);
        $headerHasFacultyRole = in_array('faculty', $headerRoles, true);
        $headerFacultyId = (int) ($request->header('X-Faculty-ID') ?? 0);
        $headerAssignedFaculty = $headerHasFacultyRole && $headerFacultyId > 0 && $headerFacultyId === (int) ($cl->intFacultyID ?? 0);

        if (!$role && $headerIsAdminOrRegistrar) {
            $role = in_array('admin', $headerRoles, true) ? 'admin' : 'registrar';
        } elseif (!$role && $headerAssignedFaculty) {
            $role = 'faculty';
        }

        // Enforce visibility policy:
        // Only registrar/admin or the assigned faculty can view classlist grades.
        $authorized = ($canEditGate || $headerIsAdminOrRegistrar || $headerAssignedFaculty);
        if (!$authorized) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: only assigned faculty or registrar/admin may view this classlist',
            ], 403);
        }

        $permissions = [
            'can_edit'       => $canEditGate || $this->canEdit($role, $assignedFaculty) || $headerIsAdminOrRegistrar || $headerAssignedFaculty,
            'can_finalize'   => $canFinalizeGate || $this->canFinalize($role, $assignedFaculty) || $headerIsAdminOrRegistrar || $headerAssignedFaculty,
            'can_unfinalize' => $canUnfinalizeGate || $this->canUnfinalize($role) || $headerIsAdminOrRegistrar,
            'role'           => $role,
        ];

        $window = $this->windows->windowInfo((int) ($cl->syid ?? 0), $id);

        $gradeOptions = [
            'midterm' => $this->buildGradeOptions((int) ($cl->grading_system_id_midterm ?? 0)),
            'finals'  => $this->buildGradeOptions((int) ($cl->grading_system_id ?? 0)),
        ];

        $students = DB::table('tb_mas_classlist_student as cls')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'cls.intStudentID')
            ->where('cls.intClassListID', $id)
            ->select(
                'cls.*',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname',
                'u.strMiddlename'
            )
            ->get();

        $out = [
            'classlist' => [
                'id' => (int) $cl->intID,
                'intFacultyID' => (int) ($cl->intFacultyID ?? 0),
                'intSubjectID' => (int) ($cl->intSubjectID ?? 0),
                'strAcademicYear' => (int) ($cl->strAcademicYear ?? 0),
                'intFinalized' => (int) ($cl->intFinalized ?? 0),
                'subject_code' => $cl->subject_code ?? null,
                'subject_description' => $cl->subject_description ?? null,
                'grading_system_id' => $cl->grading_system_id ?? null,
                'grading_system_id_midterm' => $cl->grading_system_id_midterm ?? null,
                'window' => $window,
                'permissions' => $permissions,
            ],
            'students' => $students,
            'grade_options' => $gradeOptions,
        ];

        return response()->json([
            'success' => true,
            'data'    => $out,
        ]);
    }

    /**
     * POST /api/v1/classlists/{id}/grades
     * Body: ClasslistGradeSaveRequest (period, items[], overwrite_ngs?)
     * Behavior:
     * - Registrar/Admin: always allowed
     * - Faculty (assigned): allowed only if period window active or extension set; otherwise 403
     * - Grade options:
     *   * If subject has grading system for period: set strRemarks from item.remarks when matching value
     *   * Else numeric fallback: accept integer 1..100; leave strRemarks unchanged
     */
    public function saveGrades(ClasslistGradeSaveRequest $request, int $id): JsonResponse
    {
        $cl = $this->classlists->getClasslistForGrading($id);
        if (!$cl) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        // Authorization via Gate with header-role fallback for admin/registrar
        $user = $request->user();
        $role = $this->resolveUserPrimaryRole($user);

        $headerRoles = [];
        try {
            $hdr = (string) ($request->header('X-User-Roles') ?? '');
            if ($hdr !== '') {
                $headerRoles = array_filter(array_map('trim', explode(',', strtolower($hdr))));
            }
        } catch (\Throwable $e) {
            $headerRoles = [];
        }
        $headerIsAdminOrRegistrar = in_array('admin', $headerRoles, true) || in_array('registrar', $headerRoles, true);

        $gateAllows = Gate::allows('grade.classlist.edit', $id);
        $headerFacultyId = (int) ($request->header('X-Faculty-ID') ?? 0);
        $headerHasFacultyRole = in_array('faculty', $headerRoles, true);
        $headerAssignedFaculty = $headerHasFacultyRole && $headerFacultyId > 0 && $headerFacultyId === (int) ($cl->intFacultyID ?? 0);

        if (!($headerIsAdminOrRegistrar || $gateAllows || $headerAssignedFaculty)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        // If API user context is missing but header indicates admin/registrar, use it to set bypass role
        if (!$role && $headerIsAdminOrRegistrar) {
            $role = in_array('admin', $headerRoles, true) ? 'admin' : 'registrar';
        } elseif (!$role && $headerAssignedFaculty) {
            $role = 'faculty';
        }

        $payload = $request->validated();
        $period = $payload['period'];
        $items  = $payload['items'] ?? [];
        $overwriteNGS = (bool)($payload['overwrite_ngs'] ?? false);

        // Enforce window for faculty; registrar/admin bypass
        $isBypass = in_array($role, ['registrar', 'admin'], true);
        if (!$isBypass) {
            $can = $this->windows->canEditPeriod($period, (int) ($cl->syid ?? 0), $id, 'faculty');
            if (!$can) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grading window is not active for this period',
                ], 403);
            }
        }

        // Determine mode for the requested period
        $gradingSystemId = ($period === 'midterm')
            ? (int) ($cl->grading_system_id_midterm ?? 0)
            : (int) ($cl->grading_system_id ?? 0);

        $mode = $gradingSystemId > 0 ? 'system' : 'numeric';

        $updated = 0;
        foreach ($items as $it) {
            $intCSID = (int) ($it['intCSID'] ?? 0);
            if ($intCSID <= 0) {
                continue;
            }

            $grade = $it['grade'] ?? null;

            // For numeric mode, clamp and validate 1..100
            $remarksToPersist = null;
            if ($mode === 'numeric') {
                if (!is_numeric($grade)) {
                    // Reject invalid numeric
                    continue;
                }
                $g = (int) $grade;
                if ($g < 1 || $g > 100) {
                    continue;
                }
                $grade = $g;
                // Remarks unchanged for numeric fallback
            } else {
                // System mode: match grading item and set remarks
                $gi = DB::table('tb_mas_grading_item')
                    ->where('grading_id', $gradingSystemId)
                    ->where('value', $grade)
                    ->first();
                if ($gi) {
                    $remarksToPersist = (string) ($gi->remarks ?? '');
                } else {
                    // If value doesn't match any item, skip
                    continue;
                }
            }

            // Build update data
            $col = $period === 'midterm' ? 'floatMidtermGrade' : 'floatFinalsGrade';
            $data = [
                $col => $grade,
            ];

            // Persist remarks for grading-system entries; retain for numeric fallback
            if ($remarksToPersist !== null) {
                $data['strRemarks'] = $remarksToPersist;
            }

            // When overwrite_ngs=false, avoid overwriting non-NGS values (optional safety net)
            if (!$overwriteNGS) {
                $existing = DB::table('tb_mas_classlist_student')->where('intCSID', $intCSID)->first();
                if ($existing) {
                    $cur = $period === 'midterm' ? ($existing->floatMidtermGrade ?? null) : ($existing->floatFinalsGrade ?? null);
                    if ($cur !== null && $cur !== '' && $cur !== 'NGS' && $cur !== 50) {
                        // skip overwrite
                        continue;
                    }
                }
            }

            $affected = DB::table('tb_mas_classlist_student')
                ->where('intCSID', $intCSID)
                ->update($data);

            if ($affected > 0) {
                $updated += $affected;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'updated' => $updated,
                'period' => $period,
                'classlist_id' => $id,
            ],
        ]);
    }

    /**
     * POST /api/v1/classlists/{id}/finalize
     * Body: ClasslistFinalizeRequest (period, confirm_complete?)
     * - Faculty (assigned): allowed within window; registrar/admin bypass.
     * - Transitions: 0→1 (midterm), 1→2 (finals)
     */
    public function finalize(ClasslistFinalizeRequest $request, int $id): JsonResponse
    {
        $cl = $this->classlists->getClasslistForGrading($id);
        if (!$cl) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        // Authorization via Gate with header-role fallback for admin/registrar or assigned faculty
        $user = $request->user();
        $role = $this->resolveUserPrimaryRole($user);

        $headerRoles = [];
        try {
            $hdr = (string) ($request->header('X-User-Roles') ?? '');
            if ($hdr !== '') {
                $headerRoles = array_filter(array_map('trim', explode(',', strtolower($hdr))));
            }
        } catch (\Throwable $e) {
            $headerRoles = [];
        }
        $headerIsAdminOrRegistrar = in_array('admin', $headerRoles, true) || in_array('registrar', $headerRoles, true);

        $gateAllowsFinalize = Gate::allows('grade.classlist.finalize', $id);
        $headerFacultyId = (int) ($request->header('X-Faculty-ID') ?? 0);
        $headerHasFacultyRole = in_array('faculty', $headerRoles, true);
        $headerAssignedFaculty = $headerHasFacultyRole && $headerFacultyId > 0 && $headerFacultyId === (int) ($cl->intFacultyID ?? 0);

        if (!($headerIsAdminOrRegistrar || $gateAllowsFinalize || $headerAssignedFaculty)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if (!$role && $headerIsAdminOrRegistrar) {
            $role = in_array('admin', $headerRoles, true) ? 'admin' : 'registrar';
        } elseif (!$role && $headerAssignedFaculty) {
            $role = 'faculty';
        }

        $payload = $request->validated();
        $period  = $payload['period'];

        // Window enforcement for faculty
        $isBypass = in_array($role, ['registrar', 'admin'], true);
        if (!$isBypass) {
            $can = $this->windows->canEditPeriod($period, (int) ($cl->syid ?? 0), $id, 'faculty');
            if (!$can) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grading window is not active for this period',
                ], 403);
            }
        }

        // Prevent finalization when there are students without a grade for the requested period
        $col = $period === 'midterm' ? 'floatMidtermGrade' : 'floatFinalsGrade';
        $missingCount = DB::table('tb_mas_classlist_student')
            ->where('intClassListID', $id)
            ->where(function ($q) use ($col) {
                $q->whereNull($col)
                  ->orWhere($col, '')
                  ->orWhere($col, 'NGS')
                  ->orWhere($col, 50)
                  ->orWhere($col, '50');
            })
            ->count();

        if ($missingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot finalize: ' . $missingCount . ' student(s) missing ' . $period . ' grade(s).',
                'data' => [
                    'classlist_id' => $id,
                    'period' => $period,
                    'missing_count' => $missingCount,
                ],
            ], 422);
        }

        $current = (int) ($cl->intFinalized ?? 0);
        $new = $current;

        if ($period === 'midterm') {
            if ($current === 0) {
                $new = 1;
            }
        } else {
            // finals
            if ($current >= 1) {
                $new = 2;
            }
        }

        if ($new === $current) {
            return response()->json([
                'success' => true,
                'data' => [
                    'classlist_id' => $id,
                    'intFinalized' => $current,
                    'message' => 'No state change',
                ]
            ]);
        }

        DB::table('tb_mas_classlist')->where('intID', $id)->update(['intFinalized' => $new]);

        return response()->json([
            'success' => true,
            'data' => [
                'classlist_id' => $id,
                'intFinalized' => $new,
            ],
        ]);
    }

    /**
     * POST /api/v1/classlists/{id}/unfinalize
     * - Registrar/Admin only: reverse state (2→1 or 1→0)
     */
    public function unfinalize(Request $request, int $id): JsonResponse
    {
        $cl = $this->classlists->getClasslistForGrading($id);
        if (!$cl) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        // Authorization via Gate with header-role fallback for admin/registrar
        $headerRoles = [];
        try {
            $hdr = (string) ($request->header('X-User-Roles') ?? '');
            if ($hdr !== '') {
                $headerRoles = array_filter(array_map('trim', explode(',', strtolower($hdr))));
            }
        } catch (\Throwable $e) {
            $headerRoles = [];
        }
        $headerIsAdminOrRegistrar = in_array('admin', $headerRoles, true) || in_array('registrar', $headerRoles, true);

        if (!($headerIsAdminOrRegistrar || Gate::allows('grade.classlist.unfinalize', $id))) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        $current = (int) ($cl->intFinalized ?? 0);
        $new = max(0, $current - 1);

        if ($new === $current) {
            return response()->json([
                'success' => true,
                'data' => [
                    'classlist_id' => $id,
                    'intFinalized' => $current,
                    'message' => 'No state change',
                ],
            ]);
        }

        DB::table('tb_mas_classlist')->where('intID', $id)->update(['intFinalized' => $new]);

        return response()->json([
            'success' => true,
            'data' => [
                'classlist_id' => $id,
                'intFinalized' => $new,
            ],
        ]);
    }

    // ----------------------
    // Helpers
    // ----------------------

    protected function buildGradeOptions(int $gradingSystemId): array
    {
        if ($gradingSystemId <= 0) {
            return [
                'mode' => 'numeric',
                'min'  => 1,
                'max'  => 100,
            ];
        }

        $items = DB::table('tb_mas_grading_item')
            ->where('grading_id', $gradingSystemId)
            ->orderBy('value', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'value' => $r->value,
                    'remarks' => (string) ($r->remarks ?? ''),
                ];
            });

        if ($items->count() === 0) {
            // Fallback to numeric if no items found
            return [
                'mode' => 'numeric',
                'min'  => 1,
                'max'  => 100,
            ];
        }

        return [
            'mode' => 'system',
            'grading_id' => $gradingSystemId,
            'items' => $items,
        ];
    }

    protected function resolveUserPrimaryRole($user): ?string
    {
        try {
            if (!$user) return null;
            if (method_exists($user, 'hasAnyRole')) {
                if ($user->hasAnyRole(['admin'])) return 'admin';
                if ($user->hasAnyRole(['registrar'])) return 'registrar';
            }
            if (method_exists($user, 'hasRole')) {
                if ($user->hasRole('admin')) return 'admin';
                if ($user->hasRole('registrar')) return 'registrar';
            }
            if (isset($user->role_codes)) {
                $codes = array_map('strtolower', (array) $user->role_codes);
                if (in_array('admin', $codes, true)) return 'admin';
                if (in_array('registrar', $codes, true)) return 'registrar';
            }
            // Default to faculty if none
            return 'faculty';
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function isAssignedFaculty($user, $classlist): bool
    {
        try {
            if (!$user || !$classlist) return false;
            $uid = (int) ($user->intID ?? 0);
            return $uid > 0 && $uid === (int) ($classlist->intFacultyID ?? 0);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function canEdit(?string $role, bool $assignedFaculty): bool
    {
        if (in_array($role, ['admin', 'registrar'], true)) return true;
        return $assignedFaculty;
    }

    protected function canFinalize(?string $role, bool $assignedFaculty): bool
    {
        if (in_array($role, ['admin', 'registrar'], true)) return true;
        return $assignedFaculty;
    }

    protected function canUnfinalize(?string $role): bool
    {
        return in_array($role, ['admin', 'registrar'], true);
    }
}
