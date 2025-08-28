<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ClasslistService
{
    /**
     * Retrieve grading meta by department.
     * Returns:
     * [
     *   'terms' => Collection(tb_mas_sy rows),
     *   'faculty' => Collection(tb_mas_faculty rows teaching=1)
     * ]
     */
    public function getGradingMeta(string $dept): array
    {
        $terms = DB::table('tb_mas_sy')->where('term_student_type', $dept)->get();
        $faculty = DB::table('tb_mas_faculty')->where('teaching', 1)->get();

        return [
            'terms' => $terms,
            'faculty' => $faculty,
        ];
    }

    /**
     * Retrieve grading sections and subjects by term (syid).
     * Returns:
     * [
     *   'sections' => Collection,
     *   'subjects' => Collection
     * ]
     */
    public function getGradingSections(string $term): array
    {
        $sections = DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')    
            ->where('strAcademicYear', $term)            
            ->select('cl.intID', 'cl.strClassName', 'cl.year', 'cl.strSection', 'cl.sub_section','cl.sectionCode','s.strCode')
            ->orderBy('strClassName')
            ->orderBy('year')
            ->orderBy('strSection')
            ->get();

        $subjects = DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('cl.strAcademicYear', $term)
            ->select('s.intID as subject_id', 's.strCode', 's.strDescription')
            ->groupBy('s.intID', 's.strCode', 's.strDescription')
            ->orderBy('s.strCode')
            ->get();

        return [
            'sections' => $sections,
            'subjects' => $subjects,
        ];
    }

    /**
     * Retrieve grading results based on filters.
     * Expected $filters keys:
     * - term (required), faculty?, subject?, section?, year?, class_name?, sub_section?
     * Returns:
     * [
     *   'results' => Collection
     * ]
     */
    public function getGradingResults(array $filters): array
    {
        $where = [
            ['tb_mas_classlist.strAcademicYear', '=', $filters['term']],
            ['tb_mas_classlist.isDissolved', '=', 0],
        ];

        $q = DB::table('tb_mas_classlist')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'tb_mas_classlist.intSubjectID')
            ->join('tb_mas_faculty as f', 'f.intID', '=', 'tb_mas_classlist.intFacultyID')
            ->select(
                'tb_mas_classlist.*',
                's.strCode',
                's.strDescription',
                'f.strLastname',
                'f.strFirstname'
            )
            ->where($where);

        $opt = [
            'faculty' => 'intFacultyID',
            'subject' => 'intSubjectID',
            'section' => 'strSection',
            'year' => 'year',
            'class_name' => 'strClassName',
            'sub_section' => 'sub_section'
        ];

        foreach ($opt as $key => $column) {
            if (isset($filters[$key]) && $filters[$key] !== 'undefined' && $filters[$key] !== null && $filters[$key] !== '') {
                $q->where("tb_mas_classlist.$column", $filters[$key]);
            }
        }

        $results = $q->get();

        return [
            'results' => $results,
        ];
    }

    /**
     * Retrieve submitted classlist details (students + classlist row).
     * Returns:
     * [
     *   'classlist' => object|null,
     *   'students' => Collection
     * ]
     */
    public function getClasslistSubmitted(int $classlistId): array
    {
        $classlist = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();

        $students = DB::table('tb_mas_classlist_student as cls')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'cls.intStudentID')
            ->where('cls.intClassListID', $classlistId)
            ->select(
                'cls.*',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname',
                'u.strMiddlename'
            )
            ->get();

        return [
            'classlist' => $classlist,
            'students' => $students,
        ];
    }

    /**
     * Placeholder for classlist operations (add/drop/shift/revert).
     * Will be implemented in a later pass.
     */
    /**
     * Retrieve classlist row with subject grading configuration and term info
     * for grading viewer and operations.
     *
     * @param int $classlistId
     * @return object|null
     */
    public function getClasslistForGrading(int $classlistId): ?object
    {
        return DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'cl.strAcademicYear')
            ->where('cl.intID', $classlistId)
            ->select(
                'cl.*',
                's.strCode as subject_code',
                's.strDescription as subject_description',
                's.grading_system_id',
                's.grading_system_id_midterm',
                'sy.intID as syid',
                'sy.midterm_start',
                'sy.midterm_end',
                'sy.final_start',
                'sy.final_end'
            )
            ->first();
    }

    public function classlistOp(array $payload): array
    {
        return [
            'success' => false,
            'message' => 'Not Implemented',
        ];
    }
}
