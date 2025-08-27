<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AcademicRecordService;

class PrerequisiteService
{
    protected AcademicRecordService $recordService;

    public function __construct(AcademicRecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * Check if a student has passed all prerequisites for a given subject.
     *
     * @param int $studentId The student's ID
     * @param int $subjectId The subject ID to check prerequisites for
     * @param string|null $program Optional program filter for prerequisites
     * @return array Result with validation status and details
     */
    public function checkPrerequisites(int $studentId, int $subjectId, ?string $program = null): array
    {
        // Get all prerequisites for the subject
        $prerequisites = $this->getSubjectPrerequisites($subjectId, $program);
        
        if (empty($prerequisites)) {
            return [
                'passed' => true,
                'message' => 'No prerequisites required',
                'missing_prerequisites' => [],
                'all_prerequisites' => []
            ];
        }

        // Check each prerequisite
        $missingPrerequisites = [];
        $allPrerequisites = [];
        
        foreach ($prerequisites as $prereq) {
            $prereqInfo = [
                'id' => $prereq->intPrerequisiteID,
                'code' => $prereq->code,
                'description' => $prereq->description,
                'program' => $prereq->program
            ];
            
            $allPrerequisites[] = $prereqInfo;
            
            $hasPassed = $this->recordService->hasStudentPassedSubject($studentId, $prereq->intPrerequisiteID);
            
            if (!$hasPassed) {
                $missingPrerequisites[] = $prereqInfo;
            }
        }

        $passed = empty($missingPrerequisites);
        
        return [
            'passed' => $passed,
            'message' => $passed 
                ? 'All prerequisites satisfied' 
                : 'Missing ' . count($missingPrerequisites) . ' prerequisite(s)',
            'missing_prerequisites' => $missingPrerequisites,
            'all_prerequisites' => $allPrerequisites
        ];
    }

    /**
     * Get all prerequisites for a subject.
     *
     * @param int $subjectId
     * @param string|null $program
     * @return \Illuminate\Support\Collection
     */
    protected function getSubjectPrerequisites(int $subjectId, ?string $program = null)
    {
        $query = DB::table('tb_mas_prerequisites as p')
            ->leftJoin('tb_mas_subjects as s', 's.intID', '=', 'p.intPrerequisiteID')
            ->where('p.intSubjectID', $subjectId)
            ->select([
                'p.intID',
                'p.intPrerequisiteID',
                'p.program',
                's.strCode as code',
                's.strDescription as description'
            ]);

        // Filter by program if specified
        if ($program !== null) {
            $query->where(function ($q) use ($program) {
                $q->where('p.program', $program)
                  ->orWhereNull('p.program')
                  ->orWhere('p.program', '');
            });
        }

        return $query->get();
    }

    /**
     * Delegate to AcademicRecordService.
     */
    protected function hasStudentPassedSubject(int $studentId, int $subjectId): bool
    {
        return $this->recordService->hasStudentPassedSubject($studentId, $subjectId);
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

    /**
     * Batch check prerequisites for multiple subjects for a student.
     *
     * @param int $studentId
     * @param array $subjectIds
     * @param string|null $program
     * @return array
     */
    public function batchCheckPrerequisites(int $studentId, array $subjectIds, ?string $program = null): array
    {
        $results = [];
        
        foreach ($subjectIds as $subjectId) {
            $results[$subjectId] = $this->checkPrerequisites($studentId, $subjectId, $program);
        }
        
        return $results;
    }

    /**
     * Get prerequisite validation result for a classlist (includes subject lookup).
     *
     * @param int $studentId
     * @param int $classlistId
     * @return array
     */
    public function checkPrerequisitesForClasslist(int $studentId, int $classlistId): array
    {
        // Get subject ID from classlist
        $classlist = DB::table('tb_mas_classlist')
            ->where('intID', $classlistId)
            ->select('intSubjectID')
            ->first();

        if (!$classlist) {
            return [
                'passed' => false,
                'message' => 'Classlist not found',
                'missing_prerequisites' => [],
                'all_prerequisites' => []
            ];
        }

        // Get student's program for prerequisite filtering
        $student = DB::table('tb_mas_users')
            ->where('intID', $studentId)
            ->select('intProgramID')
            ->first();

        $program = $student ? (string)$student->intProgramID : null;

        return $this->checkPrerequisites($studentId, $classlist->intSubjectID, $program);
    }
}
