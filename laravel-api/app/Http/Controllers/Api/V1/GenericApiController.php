<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenericApiController extends Controller
{
    /**
     * GET /api/v1/generic/faculty
     * Query params:
     *  - q?: string (search by name)
     *  - id?: int (exact id lookup)
     *
     * Returns list of faculty with minimal fields for parity-style lookups.
     */
    public function faculty(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'q'         => 'sometimes|string',
            'id'        => 'sometimes|integer',
            'teaching'  => 'sometimes|integer|in:0,1',
            'campus_id' => 'sometimes|integer',
        ]);

        $q = DB::table('tb_mas_faculty');

        if (!empty($payload['id'])) {
            $q->where('intID', (int) $payload['id']);
        } elseif (!empty($payload['q'])) {
            $term = '%' . $payload['q'] . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('strFirstname', 'like', $term)
                    ->orWhere('strLastname', 'like', $term)
                    ->orWhere('strMiddlename', 'like', $term);
            });
        }

        // Apply teaching filter:
        // - If explicit ?teaching=0/1 is provided, honor it.
        // - Otherwise, default to teaching=1 when neither id nor q is provided (dropdown use-case).
        if (array_key_exists('teaching', $payload)) {
            $q->where('teaching', (int) $payload['teaching']);
        } elseif (empty($payload['id']) && empty($payload['q'])) {
            $q->where('teaching', 1);
        }

        // Restrict to advisors whose departments overlap with the actor (from X-Faculty-ID)
        // Overlap rule here is department-only (campus-insensitive), per requirement.
        try {
            $actorIdRaw = $request->header('X-Faculty-ID', $request->input('faculty_id'));
            $actorId = ($actorIdRaw !== null && $actorIdRaw !== '' && is_numeric($actorIdRaw)) ? (int) $actorIdRaw : null;
            if ($actorId) {
                $actorDepts = DB::table('tb_mas_faculty_departments')
                    ->where('intFacultyID', $actorId)
                    ->pluck('department_code')
                    ->map(function ($v) { return strtolower(trim((string) $v)); })
                    ->filter(function ($v) { return $v !== ''; })
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($actorDepts)) {
                    // Candidate must have at least one matching department tag
                    $q->whereExists(function ($sub) use ($actorDepts) {
                        $sub->select(DB::raw(1))
                            ->from('tb_mas_faculty_departments as fd')
                            ->whereColumn('fd.intFacultyID', 'tb_mas_faculty.intID')
                            ->whereIn(DB::raw('LOWER(fd.department_code)'), $actorDepts);
                    });
                } else {
                    // Actor has no department tags -> no overlap with anyone; return empty set
                    $q->whereRaw('1 = 0');
                }
            }
        } catch (\Throwable $e) {
            // fail-open (no restriction) on any unexpected error
        }

        // Optional campus filter
        if (array_key_exists('campus_id', $payload)) {
            $q->where('campus_id', (int) $payload['campus_id']);
        }

        $rows = $q->orderBy('strLastname')
            ->orderBy('strFirstname')
            ->select(
                'intID',
                'strFirstname',
                'strMiddlename',
                'strLastname',
                'teaching'
            )
            ->get()
            ->map(function ($r) {
                $full = trim(implode(' ', array_filter([
                    $r->strFirstname ?? '',
                    $r->strMiddlename ?? '',
                    $r->strLastname ?? '',
                ])));
                return [
                    'id'          => $r->intID,
                    'first_name'  => $r->strFirstname,
                    'middle_name' => $r->strMiddlename,
                    'last_name'   => $r->strLastname,
                    'full_name'   => $full,
                    'teaching'    => (int) ($r->teaching ?? 0),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    /**
     * GET /api/v1/generic/terms
     * Returns list of terms (tb_mas_sy) with essential fields for dropdowns.
     */
    public function terms(Request $request): JsonResponse
    {
        $campusId = $request->query('campus_id');

        $q = DB::table('tb_mas_sy')
            ->orderBy('strYearStart', 'desc')
            ->orderBy('enumSem', 'asc');

        if ($campusId !== null && $campusId !== '') {
            $q->where('campus_id', (int) $campusId);
        }

        $rows = $q->select(
                'intID',
                'enumSem',
                'strYearStart',
                'strYearEnd',
                'term_label',
                'term_student_type',
                'campus_id'
            )
            ->get()
            ->map(function ($r) {
                $label = sprintf(
                    '%s %s %s-%s %s',
                    $r->enumSem,
                    ($r->term_label === 'Semester' ? 'Sem' : $r->term_label),
                    $r->strYearStart,
                    $r->strYearEnd,
                    $r->term_student_type
                );
                return [
                    'intID'             => $r->intID,
                    'enumSem'           => $r->enumSem,
                    'strYearStart'      => $r->strYearStart,
                    'strYearEnd'        => $r->strYearEnd,
                    'term_label'        => $r->term_label,
                    'term_student_type' => $r->term_student_type ?? null,
                    'campus_id'         => $r->campus_id ?? null,
                    'label'             => $label,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }

    /**
     * GET /api/v1/generic/active-term
     * Returns the current active term (most recent by year/semester).
     */
    public function activeTerm(Request $request): JsonResponse
    {
        $campusId = $request->query('campus_id');

        $q = DB::table('tb_mas_sy')
            ->orderBy('strYearStart', 'desc')
            ->orderBy('enumSem', 'asc');

        if ($campusId !== null && $campusId !== '') {
            $q->where('campus_id', (int) $campusId);
        }

        $activeTerm = $q->select(
                'intID',
                'enumSem',
                'strYearStart',
                'strYearEnd',
                'term_label',
                'term_student_type',
                'campus_id'
            )
            ->first();

        if (!$activeTerm) {
            return response()->json([
                'success' => false,
                'message' => 'No active term found',
                'data'    => null,
            ], 404);
        }

        $label = sprintf(
            '%s %s %s-%s',
            $activeTerm->enumSem,
            ($activeTerm->term_label === 'Semester' ? 'Sem' : $activeTerm->term_label),
            $activeTerm->strYearStart,
            $activeTerm->strYearEnd
        );

        $termData = [
            'intID'             => $activeTerm->intID,
            'enumSem'           => $activeTerm->enumSem,
            'strYearStart'      => $activeTerm->strYearStart,
            'strYearEnd'        => $activeTerm->strYearEnd,
            'term_label'        => $activeTerm->term_label,
            'term_student_type' => $activeTerm->term_student_type ?? null,
            'campus_id'         => $activeTerm->campus_id ?? null,
            'label'             => $label,
        ];

        return response()->json([
            'success' => true,
            'data'    => $termData,
        ]);
    }
}
