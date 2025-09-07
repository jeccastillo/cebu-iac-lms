<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreditedSubjectsService
{
    /**
     * List credited subjects for a student by student_number.
     * Returns array of rows joined with tb_mas_subjects for code/description.
     *
     * @param string $studentNumber
     * @return array
     */
    public function list(string $studentNumber): array
    {
        $student = DB::table('tb_mas_users')
            ->where('strStudentNumber', $studentNumber)
            ->select('intID')
            ->first();

        if (!$student) {
            throw new \InvalidArgumentException('Student not found');
        }

        $rows = DB::table('tb_mas_classlist_student as cls')
            ->leftJoin('tb_mas_subjects as s', 's.intID', '=', 'cls.equivalent_subject')
            ->where('cls.intStudentID', (int) $student->intID)
            ->where('cls.is_credited_subject', 1)
            ->select([
                'cls.intCSID as id',
                'cls.equivalent_subject',
                'cls.term_taken',
                'cls.school_taken',
                'cls.strRemarks',
                'cls.credited_subject_name',
                's.strCode as subject_code',
                's.strDescription as subject_description',
            ])
            ->orderBy('cls.intCSID', 'desc')
            ->get();

        return $rows->map(function ($r) {
            return [
                'id' => (int) $r->id,
                'subject_id' => $r->equivalent_subject !== null ? (int) $r->equivalent_subject : null,
                'subject_code' => $r->subject_code,
                'subject_description' => $r->subject_description,
                'credited_subject_name' => $r->credited_subject_name,
                'term_taken' => $r->term_taken,
                'school_taken' => $r->school_taken,
                'remarks' => $r->strRemarks,
            ];
        })->toArray();
    }

    /**
     * Create a credited subject entry for a student by student_number.
     * Prevents duplicates for same subject credit.
     *
     * @param string  $studentNumber
     * @param int     $subjectId
     * @param ?string $termTaken
     * @param ?string $schoolTaken
     * @param ?string $remarks
     * @param Request $request
     * @return array Created row (id and details)
     */
    public function create(string $studentNumber, int $subjectId, ?string $termTaken, ?string $schoolTaken, ?string $remarks, Request $request): array
    {
        $student = DB::table('tb_mas_users')
            ->where('strStudentNumber', $studentNumber)
            ->select('intID')
            ->first();

        if (!$student) {
            throw new \InvalidArgumentException('Student not found');
        }

        // Validate subject exists
        $subject = DB::table('tb_mas_subjects')->where('intID', $subjectId)->first();
        if (!$subject) {
            throw new \InvalidArgumentException('Subject not found');
        }

        // Prevent duplicates
        $exists = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', (int) $student->intID)
            ->where('is_credited_subject', 1)
            ->where('equivalent_subject', $subjectId)
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException('Credited subject already exists for this student');
        }

        // Prepare row payload
        $row = [
            'intStudentID'          => (int) $student->intID,
            'is_credited_subject'   => 1,
            'equivalent_subject'    => (int) $subjectId,
            'credited_subject_name' => $this->buildCreditedName($subject),
            'term_taken'            => $termTaken !== '' ? $termTaken : null,
            'school_taken'          => $schoolTaken !== '' ? $schoolTaken : null,
            'strRemarks'            => ($remarks !== null && $remarks !== '') ? $remarks : 'credited',
        ];

        // Some schemas enforce non-null intClassListID; if present, set to 0 sentinel.
        if (Schema::hasColumn('tb_mas_classlist_student', 'intClassListID') && !array_key_exists('intClassListID', $row)) {
            $row['intClassListID'] = 0;
        }
        // Optional: set intsyID to NULL (do not scope to a term)
        if (Schema::hasColumn('tb_mas_classlist_student', 'intsyID') && !array_key_exists('intsyID', $row)) {
            $row['intsyID'] = null;
        }

        $id = DB::table('tb_mas_classlist_student')->insertGetId($row);

        // System log
        try {
            \App\Services\SystemLogService::log('create', 'ClasslistStudent', $id, null, array_merge(['intCSID' => $id], $row), $request);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return [
            'id' => (int) $id,
            'student_id' => (int) $student->intID,
            'subject_id' => (int) $subjectId,
            'term_taken' => $row['term_taken'],
            'school_taken' => $row['school_taken'],
            'remarks' => $row['strRemarks'],
            'credited_subject_name' => $row['credited_subject_name'],
        ];
    }

    /**
     * Delete a credited subject entry by id ensuring ownership and credited flag.
     *
     * @param string  $studentNumber
     * @param int     $creditId
     * @param Request $request
     * @return bool
     */
    public function delete(string $studentNumber, int $creditId, Request $request): bool
    {
        $student = DB::table('tb_mas_users')
            ->where('strStudentNumber', $studentNumber)
            ->select('intID')
            ->first();

        if (!$student) {
            throw new \InvalidArgumentException('Student not found');
        }

        $existing = DB::table('tb_mas_classlist_student')
            ->where('intCSID', $creditId)
            ->first();

        if (!$existing || (int) $existing->intStudentID !== (int) $student->intID || (int) $existing->is_credited_subject !== 1) {
            throw new \InvalidArgumentException('Credited subject entry not found for this student');
        }

        // System log pre-image
        $before = (array) $existing;

        DB::table('tb_mas_classlist_student')->where('intCSID', $creditId)->delete();

        try {
            \App\Services\SystemLogService::log('delete', 'ClasslistStudent', (int) $creditId, $before, null, $request);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return true;
    }

    /**
     * Return a list of equivalent subject ids for a given subject.
     *
     * @param int $subjectId
     * @return array<int>
     */
    public function subjectEquivalentsFor(int $subjectId): array
    {
        $rows = DB::table('tb_mas_equivalents')
            ->select(['intSubjectID', 'intEquivalentID'])
            ->where('intSubjectID', $subjectId)
            ->orWhere('intEquivalentID', $subjectId)
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $a = (int) ($r->intSubjectID ?? 0);
            $b = (int) ($r->intEquivalentID ?? 0);
            if ($a === $subjectId && $b > 0) $out[] = $b;
            if ($b === $subjectId && $a > 0) $out[] = $a;
        }
        return array_values(array_unique($out));
    }

    protected function buildCreditedName(object $subject): string
    {
        $code = (string) ($subject->strCode ?? '');
        $desc = (string) ($subject->strDescription ?? '');
        $name = trim($code . ' - ' . $desc);
        return $name !== '' ? $name : 'Credited Subject';
    }
}
