<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AcademicRecordService
{
    /**
     * Check if a student has passed a specific subject.
     * A student passes if:
     * 1. Final grade <= 3.0 (numeric grade)
     * 2. Remarks contains "passed" (case insensitive)
     * 3. Remarks "credit"/"credited" count as pass
     *
     * @param int $studentId
     * @param int $subjectId
     * @return bool
     */
    public function hasStudentPassedSubject(int $studentId, int $subjectId): bool
    {
        // 1) Check credited subjects (direct and via equivalents)
        // Build set of equivalent subject IDs for the requested subject
        $equivRows = DB::table('tb_mas_equivalents')
            ->select(['intSubjectID', 'intEquivalentID'])
            ->where('intSubjectID', $subjectId)
            ->orWhere('intEquivalentID', $subjectId)
            ->get();

        $equivIds = [];
        foreach ($equivRows as $r) {
            $a = (int)($r->intSubjectID ?? 0);
            $b = (int)($r->intEquivalentID ?? 0);
            if ($a === $subjectId && $b > 0) {
                $equivIds[] = $b;
            }
            if ($b === $subjectId && $a > 0) {
                $equivIds[] = $a;
            }
        }
        $checkIds = array_unique(array_merge([$subjectId], $equivIds));

        $hasCredit = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', $studentId)
            ->where('is_credited_subject', 1)
            ->whereIn('equivalent_subject', $checkIds)
            ->exists();

        if ($hasCredit) {
            return true;
        }

        // 2) Fallback to academic records (grades/remarks)
        $records = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
            ->where('cls.intStudentID', $studentId)
            ->where('cl.intSubjectID', $subjectId)
            ->select([
                'cls.floatFinalGrade',
                'cls.strRemarks'
            ])
            ->get();

        if ($records->isEmpty()) {
            return false;
        }

        foreach ($records as $record) {
            if ($this->isPassingRecord($record)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a single academic record represents a passing grade.
     *
     * @param object $record
     * @return bool
     */
    protected function isPassingRecord($record): bool
    {
        $finalGrade = $record->floatFinalGrade;
        $remarks = strtolower(trim((string)($record->strRemarks ?? '')));

        // Check final grade (numeric)
        if ($finalGrade !== null && is_numeric($finalGrade)) {
            $grade = (float)$finalGrade;
            if ($grade > 0 && $grade <= 3.0) {
                return true;
            }
        }

        // Check remarks for explicit pass indicators
        $passIndicators = ['passed', 'pass', 'p', 'credit', 'credited'];
        foreach ($passIndicators as $indicator) {
            if (strpos($remarks, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }
}
