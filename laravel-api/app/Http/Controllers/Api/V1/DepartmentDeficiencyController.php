<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DepartmentDeficiencyStoreRequest;
use App\Http\Requests\Api\V1\DepartmentDeficiencyUpdateRequest;
use App\Http\Resources\DepartmentDeficiencyResource;
use App\Models\FacultyDepartment;
use App\Models\PaymentDescription;
use App\Services\DepartmentContextService;
use App\Services\DepartmentDeficiencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentDeficiencyController extends Controller
{
    /**
     * GET /api/v1/department-deficiencies
     * Query:
     *  - student_number?: string
     *  - student_id?: int
     *  - term?: int (syid)
     *  - department_code?: string
     *  - campus_id?: int
     *  - page?: int
     *  - per_page?: int
     */
    public function index(
        Request $request,
        DepartmentContextService $ctx,
        DepartmentDeficiencyService $svc
    ): JsonResponse {
        $studentNumber  = $request->query('student_number');
        $studentId      = $request->query('student_id');
        $syid           = $request->query('term');
        $dept           = $request->query('department_code');
        $page           = (int) ($request->query('page', 1));
        $perPage        = (int) ($request->query('per_page', 25));
        $campusId       = $ctx->resolveCampusId($request);

        [$actorId, $isAdmin] = $this->resolveActor($request);
        $allowedDepartments = $isAdmin
            ? []
            : ($actorId ? FacultyDepartment::allowedForFaculty($actorId, $campusId) : []);

        $out = $svc->list(
            $studentNumber !== null ? (string) $studentNumber : null,
            $studentId !== null ? (int) $studentId : null,
            $syid !== null ? (int) $syid : null,
            $dept !== null ? (string) $dept : null,
            $campusId,
            $page,
            $perPage,
            $allowedDepartments
        );

        return response()->json([
            'success' => true,
            'data'    => $out['items'],
            'meta'    => $out['meta'],
        ]);
    }

    /**
     * GET /api/v1/department-deficiencies/{id}
     */
    public function show(
        int $id,
        Request $request,
        DepartmentDeficiencyService $svc
    ): JsonResponse {
        $row = $svc->get($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Deficiency not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * GET /api/v1/department-deficiencies/meta
     * Returns options for UI:
     *  - departments: allowed department codes (all if admin)
     *  - payment_descriptions: campus-scoped PDs (id,name,amount)
     */
    public function meta(
        Request $request,
        DepartmentContextService $ctx
    ): JsonResponse {
        $campusId = $ctx->resolveCampusId($request);
        [$actorId, $isAdmin] = $this->resolveActor($request);

        // Populate departments dropdown ONLY with departments explicitly assigned to the acting faculty.
        // Ignore admin bypass for this meta endpoint to avoid showing unassigned departments.
        $codes = $ctx->departmentCodes();
        $assigned = $actorId ? FacultyDepartment::allowedForFaculty($actorId, $campusId) : [];
        if(!$isAdmin)
            $allowed = array_values(array_intersect($codes, array_map('strtolower', (array) $assigned)));
        else
            $allowed = $codes;
        // Payment descriptions scoped by campus (parity with PaymentDescriptionController)
        $pdQ = PaymentDescription::query();
        if ($campusId !== null) {
            $pdQ->where('campus_id', (int) $campusId);
        }
        $pds = $pdQ->orderBy('name', 'asc')->get(['intID as id', 'name', 'amount']);

        return response()->json([
            'success' => true,
            'data'    => [
                'departments'          => $allowed,
                'payment_descriptions' => $pds,
            ],
        ]);
    }

    /**
     * POST /api/v1/department-deficiencies
     */
    public function store(
        DepartmentDeficiencyStoreRequest $request,
        DepartmentContextService $ctx,
        DepartmentDeficiencyService $svc
    ): JsonResponse {
        $data = $request->validated();

        [$actorId,] = $this->resolveActor($request);
        $campusId   = $ctx->resolveCampusId($request);

        $row = $svc->store($data, [
            'faculty_id' => $actorId,
            'campus_id'  => $campusId,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $row,
        ], 201);
    }

    /**
     * PUT /api/v1/department-deficiencies/{id}
     */
    public function update(
        int $id,
        DepartmentDeficiencyUpdateRequest $request,
        DepartmentDeficiencyService $svc
    ): JsonResponse {
        [$actorId,] = $this->resolveActor($request);
        $old = $svc->get($id);
        $row = $svc->update($id, $request->validated(), $actorId);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Deficiency not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data'    => $row,
            'meta'    => [ 'old' => $old ],
        ]);
    }

    /**
     * DELETE /api/v1/department-deficiencies/{id}
     */
    public function destroy(
        int $id,
        DepartmentDeficiencyService $svc
    ): JsonResponse {
        $existing = $svc->get($id);
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Deficiency not found',
            ], 404);
        }
        $svc->destroy($id);
        return response()->json([
            'success' => true,
            'message' => 'Deficiency deleted',
        ]);
    }

    // --------------------------
    // Helpers
    // --------------------------

    /**
     * @return array{0:?int,1:bool} [$actorId, $isAdmin]
     */
    protected function resolveActor(Request $request): array
    {
        // Prefer middleware-injected faculty
        $faculty = $request->attributes->get('faculty');
        $actorId = $faculty ? (int) ($faculty->intID ?? 0) : null;

        // Fallback to header X-Faculty-ID
        if (!$actorId) {
            $hdr = $request->header('X-Faculty-ID');
            if ($hdr !== null && $hdr !== '' && is_numeric($hdr)) {
                $actorId = (int) $hdr;
            }
        }

        // Admin from roles header (fast heuristic)
        $rolesHdr = (string) ($request->header('X-User-Roles') ?? '');
        $isAdmin = false;
        if ($rolesHdr !== '') {
            $roles = preg_split('/[,\s]+/', $rolesHdr) ?: [];
            $roles = array_values(array_unique(array_map(function ($r) {
                return strtolower(trim((string) $r));
            }, $roles)));
            $isAdmin = in_array('admin', $roles, true);
        }

        return [$actorId, $isAdmin];
    }
}
