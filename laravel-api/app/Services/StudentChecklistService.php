<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\StudentChecklist;
use App\Models\StudentChecklistItem;

/**
 * StudentChecklistService
 *
 * Generates and manages student graduation checklists.
 */
class StudentChecklistService
{
    /**
     * Generate a checklist from a curriculum for a given student, year level, and sem.
     * Includes all curriculum subjects and marks 'passed' where applicable.
     *
     * @param  int    $intStudentID
     * @param  int    $intCurriculumID
     * @return \App\Models\StudentChecklist
     */
    public function generateFromCurriculum(int $intStudentID, int $intCurriculumID): StudentChecklist
    {
        // Create a new checklist row (no checklist-level year/sem)
        $checklist = StudentChecklist::create([
            'intStudentID'    => $intStudentID,
            'intCurriculumID' => $intCurriculumID,
            'remarks'         => null,
            'created_by'      => null,
        ]);

        // Gather passed subjects for the student
        $passedMap = $this->computePassedMap($intStudentID);

        // Pull subjects from curriculum (tb_mas_curriculum_subject)
        $curriculumSubjects = DB::table('tb_mas_curriculum_subject')
            ->where('intCurriculumID', $intCurriculumID)
            ->select('intSubjectID', 'intYearLevel', 'intSem')
            ->get();

        $now = Carbon::now();

        foreach ($curriculumSubjects as $row) {
            $subjectId = (int)($row->intSubjectID ?? 0);
            if ($subjectId <= 0) {
                continue;
            }
            $isPassed = isset($passedMap[$subjectId]) && $passedMap[$subjectId] === true;

            StudentChecklistItem::create([
                'intChecklistID' => $checklist->intID,
                'intSubjectID'   => $subjectId,
                'intYearLevel'   => isset($row->intYearLevel) ? (int)$row->intYearLevel : null,
                'intSem'         => isset($row->intSem) ? (int)$row->intSem : null,
                'strStatus'      => $isPassed ? 'passed' : 'planned',
                'dteCompleted'   => $isPassed ? $now->toDateString() : null,
                'isRequired'     => 1,
            ]);
        }

        // Reload with items
        return $checklist->load('items');
    }

    /**
     * Compute a map [intSubjectID => bool] of subjects passed by the student.
     *
     * Heuristics:
     * - Consider a subject passed if:
     *   - cls.floatFinalGrade is not null AND <= 3.0
     *   - OR cls.strRemarks contains 'PASS' (case-insensitive)
     *
     * @param  int $intStudentID
     * @return array<int,bool>
     */
    public function computePassedMap(int $intStudentID): array
    {
        $rows = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('cls.intStudentID', $intStudentID)
            ->select(
                's.intID as subject_id',
                'cls.floatFinalGrade as final_grade',
                'cls.strRemarks as remarks'
            )
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $sid = (int)($r->subject_id ?? 0);
            if ($sid <= 0) continue;

            $passed = false;
            $final = $r->final_grade;
            $remarks = strtolower((string)($r->remarks ?? ''));

            if ($final !== null) {
                // If numeric final grade and <= 3.0 treat as passed
                $f = (float)$final;
                if ($f > 0 && $f <= 3.0) {
                    $passed = true;
                }
            }
            if (!$passed && $remarks !== '') {
                if (strpos($remarks, 'pass') !== false) {
                    $passed = true;
                }
            }

            if ($passed) {
                $out[$sid] = true;
            }
        }
        return $out;
    }

    /**
     * Compute a summary for checklist progress.
     *
     * @param  \App\Models\StudentChecklist $checklist
     * @return array{total:int,required:int,completed:int,remaining:int,percent:float}
     */
    public function computeSummary(StudentChecklist $checklist): array
    {
        $items = $checklist->items()->get();

        $total = $items->count();
        $required = $items->where('isRequired', 1)->count();
        $completed = $items->filter(function ($i) {
            return in_array($i->strStatus, ['passed','waived'], true);
        })->count();
        $remaining = max(0, $required - $completed);
        $percent = $required > 0 ? round(($completed / $required) * 100.0, 2) : 0.0;

        return [
            'total'     => (int)$total,
            'required'  => (int)$required,
            'completed' => (int)$completed,
            'remaining' => (int)$remaining,
            'percent'   => (float)$percent,
        ];
    }
}
