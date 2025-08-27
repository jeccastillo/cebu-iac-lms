<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\AcademicRecordService;

class CorequisiteService
{
    protected AcademicRecordService $recordService;

    public function __construct(AcademicRecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * Check if a student satisfies all corequisites for a given subject.
     * A corequisite is satisfied if:
     * - The student has already passed the coreq subject, OR
     * - The coreq subject is included in the concurrentSubjectIds (planned/enlisted same term).
     *
     * @param int $studentId
     * @param int $subjectId
     * @param string|null $program
     * @param int[] $concurrentSubjectIds subject IDs planned concurrently (same term)
     * @return array {
     *   passed: bool,
     *   message: string,
     *   missing_corequisites: array<{id:int, code:string, description:string, program:?string}>,
     *   all_corequisites: array<{id:int, code:string, description:string, program:?string}>
     * }
     */
    public function checkCorequisites(
        int $studentId,
        int $subjectId,
        ?string $program = null,
        array $concurrentSubjectIds = []
    ): array {
        $coreqs = $this->getSubjectCorequisites($subjectId, $program);

        if (empty($coreqs)) {
            return [
                'passed' => true,
                'message' => 'No corequisites required',
                'missing_corequisites' => [],
                'all_corequisites' => [],
            ];
        }

        $all = [];
        $missing = [];

        // Use a fast lookup for concurrent set
        $concurrentSet = [];
        foreach ($concurrentSubjectIds as $sid) {
            if (is_numeric($sid)) {
                $concurrentSet[(int)$sid] = true;
            }
        }

        foreach ($coreqs as $c) {
            $row = [
                'id' => $c->intCorequisiteID,
                'code' => $c->code,
                'description' => $c->description,
                'program' => $c->program,
            ];
            $all[] = $row;

            $alreadyPassed = $this->recordService->hasStudentPassedSubject($studentId, (int)$c->intCorequisiteID);
            $plannedTogether = isset($concurrentSet[(int)$c->intCorequisiteID]);

            if (!($alreadyPassed || $plannedTogether)) {
                $missing[] = $row;
            }
        }

        $passed = empty($missing);

        return [
            'passed' => $passed,
            'message' => $passed
                ? 'All corequisites satisfied'
                : 'Missing ' . count($missing) . ' corequisite(s)',
            'missing_corequisites' => $missing,
            'all_corequisites' => $all,
        ];
    }

    /**
     * Batch check corequisites for multiple subjects.
     *
     * @param int $studentId
     * @param int[] $subjectIds
     * @param string|null $program
     * @param int[] $concurrentSubjectIds
     * @return array subjectId => result array (see checkCorequisites)
     */
    public function batchCheckCorequisites(
        int $studentId,
        array $subjectIds,
        ?string $program = null,
        array $concurrentSubjectIds = []
    ): array {
        $results = [];
        foreach ($subjectIds as $sid) {
            if (!is_numeric($sid)) {
                continue;
            }
            $sid = (int)$sid;
            $results[$sid] = $this->checkCorequisites($studentId, $sid, $program, $concurrentSubjectIds);
        }
        return $results;
    }

    /**
     * Get corequisite validation for a given classlist row.
     * Uses subject lookup from tb_mas_classlist and accepts planned classlist IDs (same term)
     * to satisfy concurrent enrollment semantics.
     *
     * @param int $studentId
     * @param int $classlistId
     * @param int[] $plannedClasslistIdsForTerm classlist IDs in the same enlist batch
     * @return array same envelope as checkCorequisites()
     */
    public function checkCorequisitesForClasslist(
        int $studentId,
        int $classlistId,
        array $plannedClasslistIdsForTerm = []
    ): array {
        $classlist = DB::table('tb_mas_classlist')
            ->where('intID', $classlistId)
            ->select('intSubjectID')
            ->first();

        if (!$classlist) {
            return [
                'passed' => false,
                'message' => 'Classlist not found',
                'missing_corequisites' => [],
                'all_corequisites' => [],
            ];
        }

        $subjectId = (int)$classlist->intSubjectID;

        // Resolve planned classlist IDs to subject IDs for concurrent set
        $concurrentSubjectIds = [];
        if (!empty($plannedClasslistIdsForTerm)) {
            $rows = DB::table('tb_mas_classlist')
                ->whereIn('intID', array_values(array_filter($plannedClasslistIdsForTerm, 'is_numeric')))
                ->select('intSubjectID')
                ->get();
            foreach ($rows as $r) {
                if ($r->intSubjectID !== null && is_numeric($r->intSubjectID)) {
                    $concurrentSubjectIds[] = (int)$r->intSubjectID;
                }
            }
        }

        // Program scoping: use student's program ID if available
        $student = DB::table('tb_mas_users')
            ->where('intID', $studentId)
            ->select('intProgramID')
            ->first();
        $program = $student ? (string)$student->intProgramID : null;

        return $this->checkCorequisites($studentId, $subjectId, $program, $concurrentSubjectIds);
    }

    /**
     * Get all corequisites for a subject (optionally program-scoped).
     * Mirrors the prerequisites query but uses tb_mas_corequisites and intCorequisiteID.
     *
     * @param int $subjectId
     * @param string|null $program
     * @return \Illuminate\Support\Collection
     */
    protected function getSubjectCorequisites(int $subjectId, ?string $program = null)
    {
        $query = DB::table('tb_mas_corequisites as p')
            ->leftJoin('tb_mas_subjects as s', 's.intID', '=', 'p.intCorequisiteID')
            ->where('p.intSubjectID', $subjectId)
            ->select([
                'p.intID',
                'p.intCorequisiteID',
                'p.program',
                's.strCode as code',
                's.strDescription as description',
            ]);

        if ($program !== null) {
            $query->where(function ($q) use ($program) {
                $q->where('p.program', $program)
                    ->orWhereNull('p.program')
                    ->orWhere('p.program', '');
            });
        }

        return $query->get();
    }
}
