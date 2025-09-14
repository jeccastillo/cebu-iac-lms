<?php

namespace App\Services;

use App\Models\StudentChecklist;
use App\Models\StudentChecklistItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        // Delete any existing checklists for this student first
        StudentChecklist::where('intStudentID', $intStudentID)->delete();
        
        // Create a new checklist row (no checklist-level year/sem)
        $checklist = StudentChecklist::create([
            'intStudentID'    => $intStudentID,
            'intCurriculumID' => $intCurriculumID,
            'remarks'         => null,
            'created_by'      => null,
        ]);

        // Gather subject status maps for the student
        $passedMap = $this->computePassedMap($intStudentID);
        $enrolledMap = $this->computeEnrolledMap($intStudentID);
        $failedMap = $this->computeFailedMap($intStudentID);

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
            
            // Determine status based on priority: passed > failed > enrolled > planned
            $status = 'planned';
            $dateCompleted = null;
            
            if (isset($passedMap[$subjectId]) && $passedMap[$subjectId] === true) {
                $status = 'passed';
                $dateCompleted = $now->toDateString();
            } elseif (isset($failedMap[$subjectId]) && $failedMap[$subjectId] === true) {
                $status = 'failed';
            } elseif (isset($enrolledMap[$subjectId]) && $enrolledMap[$subjectId] === true) {
                $status = 'enrolled';
            }

            StudentChecklistItem::create([
                'intChecklistID' => $checklist->intID,
                'intSubjectID'   => $subjectId,
                'intYearLevel'   => isset($row->intYearLevel) ? (int)$row->intYearLevel : null,
                'intSem'         => isset($row->intSem) ? (int)$row->intSem : null,
                'strStatus'      => $status,
                'dteCompleted'   => $dateCompleted,
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
        // 1) Passed via grades/remarks from classlist joins
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
                // Accept 'pass', 'passed', and 'credit/credited' as pass indicators
                if (strpos($remarks, 'pass') !== false
                    || strpos($remarks, 'passed') !== false
                    || strpos($remarks, 'credit') !== false
                    || strpos($remarks, 'credited') !== false) {
                    $passed = true;
                }
            }

            if ($passed) {
                $out[$sid] = true;
            }
        }

        // 2) Passed via credited subjects (direct)
        $credited = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', $intStudentID)
            ->where('is_credited_subject', 1)
            ->pluck('equivalent_subject')
            ->filter()
            ->map(function ($v) { return (int)$v; })
            ->unique()
            ->values();

        foreach ($credited as $cid) {
            if ($cid > 0) {
                $out[$cid] = true;
            }
        }

        // 3) Equivalents mapping (credits and academic passes imply passes for equivalents)
        $seedIds = array_map('intval', array_keys($out));
        if (!empty($seedIds)) {
            $eqRows = DB::table('tb_mas_equivalents')
                ->select(['intSubjectID', 'intEquivalentID'])
                ->where(function ($q) use ($seedIds) {
                    $q->whereIn('intSubjectID', $seedIds)
                      ->orWhereIn('intEquivalentID', $seedIds);
                })
                ->get();

            foreach ($eqRows as $row) {
                $a = (int)($row->intSubjectID ?? 0);
                $b = (int)($row->intEquivalentID ?? 0);
                if ($a > 0 && $b > 0) {
                    if (isset($out[$a])) {
                        $out[$b] = true;
                    }
                    if (isset($out[$b])) {
                        $out[$a] = true;
                    }
                }
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

    /**
     * Check which subjects the student is currently enrolled in (without grades)
     * Returns map: subjectId => bool
     */
    private function computeEnrolledMap(int $intStudentID): array
    {
        $enrolledMap = [];

        // Check for current enrollments without final grades
        $enrolled = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cls.intClasslistID', '=', 'cl.intID')
            ->join('tb_mas_subjects as subj', 'cl.intSubjectID', '=', 'subj.intID')
            ->where('cls.intStudentID', $intStudentID)
            ->where(function($query) {
                // No final grade recorded, or final grade is null/empty
                $query->whereNull('cls.numFinalGrade')
                      ->orWhere('cls.numFinalGrade', '=', 0)
                      ->orWhere('cls.numFinalGrade', '=', '');
            })
            ->where(function($query) {
                // No completion remarks or not marked as dropped/withdrawn
                $query->whereNull('cls.strRemarks')
                      ->orWhere('cls.strRemarks', '=', '')
                      ->orWhere('cls.strRemarks', 'not like', '%drop%')
                      ->orWhere('cls.strRemarks', 'not like', '%withdraw%')
                      ->orWhere('cls.strRemarks', 'not like', '%incomplete%');
            })
            ->select('subj.intID as subject_id')
            ->get();

        foreach ($enrolled as $row) {
            $subjectId = (int)($row->subject_id ?? 0);
            if ($subjectId > 0) {
                $enrolledMap[$subjectId] = true;
            }
        }

        return $enrolledMap;
    }

    /**
     * Check which subjects the student has failed
     * Returns map: subjectId => bool
     */
    private function computeFailedMap(int $intStudentID): array
    {
        $failedMap = [];

        // Check for failed subjects (final grade > 3.0 or fail-related remarks)
        $failed = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cls.intClasslistID', '=', 'cl.intID')
            ->join('tb_mas_subjects as subj', 'cl.intSubjectID', '=', 'subj.intID')
            ->where('cls.intStudentID', $intStudentID)
            ->where(function($query) {
                $query->where(function($subQuery) {
                    // Failed by grade (> 3.0)
                    $subQuery->whereNotNull('cls.numFinalGrade')
                             ->where('cls.numFinalGrade', '>', 3.0);
                })
                ->orWhere(function($subQuery) {
                    // Failed by remarks
                    $subQuery->whereNotNull('cls.strRemarks')
                             ->where(function($remarkQuery) {
                                 $remarkQuery->where('cls.strRemarks', 'like', '%fail%')
                                             ->orWhere('cls.strRemarks', 'like', '%dropped%')
                                             ->orWhere('cls.strRemarks', 'like', '%incomplete%')
                                             ->orWhere('cls.strRemarks', 'like', '%withdraw%');
                             });
                });
            })
            ->select('subj.intID as subject_id')
            ->get();

        foreach ($failed as $row) {
            $subjectId = (int)($row->subject_id ?? 0);
            if ($subjectId > 0) {
                $failedMap[$subjectId] = true;
            }
        }

        return $failedMap;
    }
}
