<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

/**
 * Provides per-classlist slot utilization for a given term (syid).
 *
 * For each classlist row (tb_mas_classlist) in the specified term:
 *  - slots: tb_mas_classlist.slots (capacity)
 *  - enlisted_count: count of tb_mas_classlist_student rows scoped by intsyID=term
 *  - enrolled_count: enlisted students that have a tb_mas_registration row for the same term
 *                    with intROG semantics indicating enrolled (>=1) and not withdrawn/terminal.
 *  - remaining_slots: max(slots - enrolled_count, 0)
 *
 * Notes:
 *  - Dissolved classlists (isDissolved=1) are excluded by default.
 *  - Filters supported: intSubjectID, intFacultyID, section (LIKE on sectionCode), subject, class_name,
 *                      year, sub_section.
 *  - Pagination is supported via page/perPage params. Total is computed against filtered base.
 */
class ClasslistSlotsService
{
    /**
     * List classlists for a term with counts and derived remaining slots.
     *
     * @param array $params {
     *   @type int         term        Required. tb_mas_sy.intID
     *   @type int         page        Optional. default=1
     *   @type int         perPage     Optional. default=20 (capped to 100)
     *   @type int         intSubjectID Optional filter
     *   @type int         intFacultyID Optional filter
     *   @type string      section     Optional LIKE filter on sectionCode
     *   @type string      subject     Optional LIKE filter on subject code or description
     *   @type string      class_name  Optional filter on cl.strClassName
     *   @type int         year        Optional filter on cl.year
     *   @type string      sub_section Optional filter on cl.sub_section
     * }
     * @return array{data: array, meta: array{page:int, per_page:int, total:int}}
     */
    public function listByTerm(array $params): array
    {
        $term = (int) ($params['term'] ?? 0);
        if ($term <= 0) {
            throw new \InvalidArgumentException('term (syid) is required and must be > 0');
        }

        $perPage = (int) ($params['perPage'] ?? $params['per_page'] ?? 20);
        if ($perPage < 1) $perPage = 20;
        if ($perPage > 100) $perPage = 100;
        $page = (int) ($params['page'] ?? 1);
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $perPage;

        // Base query for classlists in the term (excluding dissolved)
        $base = $this->buildBaseQuery($term);
        $this->applyFilters($base, $params);

        // Total (distinct classlists) after filters
        $total = (clone $base)->distinct()->count('cl.intID');

        // Subquery: enlisted counts for the term
        $subEnlisted = DB::table('tb_mas_classlist_student as cls')
            ->select('cls.intClassListID', DB::raw('COUNT(*) as enlisted_count'))
            ->where('cls.intsyID', $term)
            ->groupBy('cls.intClassListID');

        // Subquery: enrolled counts for the term
        // intROG semantics: >=1 considered enrolled; exclude 3,5 where present (withdrawn/terminal).
        $subEnrolled = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_registration as r', function ($j) use ($term) {
                $j->on('r.intStudentID', '=', 'cls.intStudentID')
                  ->where('r.intAYID', '=', $term);
            })
            ->select('cls.intClassListID', DB::raw('COUNT(*) as enrolled_count'))
            ->where('cls.intsyID', $term)            
            ->whereIn('r.enrollment_status', ['enrolled','withdrawn after','withdrawn end'])
            ->groupBy('cls.intClassListID');

        $q = (clone $base)
            ->leftJoinSub($subEnlisted, 'enl', function ($j) {
                $j->on('enl.intClassListID', '=', 'cl.intID');
            })
            ->leftJoinSub($subEnrolled, 'enr', function ($j) {
                $j->on('enr.intClassListID', '=', 'cl.intID');
            })
            ->select([
                'cl.intID as classlist_id',
                'cl.sectionCode',
                'cl.strClassName',
                'cl.year',
                'cl.strSection',
                'cl.sub_section',
                'cl.intFinalized',
                'cl.slots',
                's.strCode as subject_code',
                's.strDescription as subject_description',
                'f.strFirstname as faculty_firstname',
                'f.strLastname as faculty_lastname',
                DB::raw('COALESCE(enl.enlisted_count, 0) as enlisted_count'),
                DB::raw('COALESCE(enr.enrolled_count, 0) as enrolled_count'),
                DB::raw('CASE WHEN cl.slots IS NULL THEN 0 WHEN (cl.slots - COALESCE(enr.enrolled_count,0)) < 0 THEN 0 ELSE (cl.slots - COALESCE(enr.enrolled_count,0)) END as remaining_slots'),
            ])
            ->orderBy('cl.intID', 'desc')
            ->offset($offset)
            ->limit($perPage);

        $rows = $q->get();

        $items = [];
        foreach ($rows as $r) {
            $fname = $r->faculty_firstname ?? null;
            $lname = $r->faculty_lastname ?? null;
            $facultyName = null;
            if ($fname || $lname) {
                $facultyName = trim((string)$fname . ' ' . (string)$lname);
            }

            $items[] = [
                'classlist_id'       => (int) $r->classlist_id,
                'section_code'       => $r->sectionCode,
                'class_name'         => $r->strClassName,
                'year'               => $this->toNullableInt($r->year),
                'section'            => $r->strSection,
                'sub_section'        => $r->sub_section,
                'subject_code'       => $r->subject_code,
                'subject_description'=> $r->subject_description,
                'faculty_name'       => $facultyName,
                'slots'              => $this->toNullableInt($r->slots),
                'enlisted_count'     => (int) ($r->enlisted_count ?? 0),
                'enrolled_count'     => (int) ($r->enrolled_count ?? 0),
                'remaining_slots'    => (int) ($r->remaining_slots ?? 0),
                'finalized'          => $this->toNullableInt($r->intFinalized),
            ];
        }

        return [
            'data' => $items,
            'meta' => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => (int) $total,
            ],
        ];
    }

    /**
     * Build the base query for classlists in a given term (excluding dissolved).
     */
    protected function buildBaseQuery(int $term): Builder
    {
        $q = DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'cl.intFacultyID')
            ->where('cl.strAcademicYear', '=', $term)
            ->where(function ($w) {
                // Exclude dissolved when column exists; if not present, the condition is ignored safely.
                $w->where('cl.isDissolved', '=', 0)
                  ->orWhereNull('cl.isDissolved');
            });

        return $q;
    }

    /**
     * Apply optional filters on the base query.
     */
    protected function applyFilters(Builder $q, array $params): void
    {
        $mapExact = [
            'intSubjectID' => 'cl.intSubjectID',
            'intFacultyID' => 'cl.intFacultyID',
            'year'         => 'cl.year',
            'sub_section'  => 'cl.sub_section',
        ];

        foreach ($mapExact as $paramKey => $column) {
            if (isset($params[$paramKey]) && $params[$paramKey] !== '' && $params[$paramKey] !== null) {
                $q->where($column, $params[$paramKey]);
            }
        }

        // Section LIKE filter on sectionCode
        if (isset($params['section']) && $params['section'] !== '' && $params['section'] !== null) {
            $needle = str_replace(['%', '_'], ['\\%', '\\_'], (string) $params['section']);
            $q->where('cl.sectionCode', 'like', '%' . $needle . '%');
        }

        // Subject LIKE filter across subject code or description
        if (isset($params['subject']) && $params['subject'] !== '' && $params['subject'] !== null) {
            $needle = str_replace(['%', '_'], ['\\%', '\\_'], (string) $params['subject']);
            $q->where(function ($w) use ($needle) {
                $w->where('s.strCode', 'like', '%' . $needle . '%')
                  ->orWhere('s.strDescription', 'like', '%' . $needle . '%');
            });
        }

        // Class name exact or LIKE (prefer exact to avoid heavy scans; use LIKE if contains wildcard)
        if (isset($params['class_name']) && $params['class_name'] !== '' && $params['class_name'] !== null) {
            $val = (string) $params['class_name'];
            if (strpos($val, '%') !== false || strpos($val, '_') !== false) {
                $q->where('cl.strClassName', 'like', $val);
            } else {
                $q->where('cl.strClassName', '=', $val);
            }
        }
    }

    protected function toNullableInt($v): ?int
    {
        if ($v === null || $v === '') return null;
        return is_numeric($v) ? (int) $v : null;
    }
}
