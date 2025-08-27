<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnlistmentService
{
    protected UserContextResolver $ctx;
    protected PrerequisiteService $prerequisiteService;
    protected CorequisiteService $corequisiteService;

    public function __construct(UserContextResolver $ctx, PrerequisiteService $prerequisiteService, CorequisiteService $corequisiteService)
    {
        $this->ctx = $ctx;
        $this->prerequisiteService = $prerequisiteService;
        $this->corequisiteService = $corequisiteService;
    }

    /**
     * Process registrar enlistment operations (add, drop, change_section) for a student and term.
     * Returns a structured result with per-operation outcomes and the current enlisted snapshot.
     *
     * @param array   $payload Validated payload (UnityEnlistRequest)
     * @param Request $request
     * @return array
     */
    public function enlist(array $payload, Request $request): array
    {
        $studentNumber = $payload['student_number'];
        $term          = (int) $payload['term'];
        $yearLevel     = (int) $payload['year_level'];
        $studentType   = $payload['student_type'] ?? 'continuing';
        $operations    = $payload['operations'] ?? [];

        // Resolve student
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            return [
                'success'    => false,
                'message'    => 'Student not found',
                'operations' => [],
                'current'    => [],
            ];
        }
        $studentId = (int) $user->intID;

        // Resolve acting user (registrar) to stamp enlisted_user
        $actorId = $this->ctx->resolveUserId($request);
        if ($actorId === null) {
            // Fallback header used by frontend
            $xfac = $request->header('X-Faculty-ID');
            if ($xfac !== null && is_numeric($xfac)) {
                $actorId = (int) $xfac;
            }
        }

        $plannedAddClasslistIds = [];
        foreach ($operations as $op) {
            $typePl = $op['type'] ?? null;
            if ($typePl === 'add') {
                $cid = (int) ($op['classlist_id'] ?? 0);
                if ($cid > 0) {
                    $plannedAddClasslistIds[] = $cid;
                }
            } elseif ($typePl === 'change_section') {
                $toCid = (int) ($op['to_classlist_id'] ?? 0);
                if ($toCid > 0) {
                    $plannedAddClasslistIds[] = $toCid;
                }
            }
        }
        $plannedAddClasslistIds = array_values(array_unique($plannedAddClasslistIds));

        $results = [];
        $okAll   = true;

        DB::beginTransaction();
        try {
            // Ensure tb_mas_registration row exists/up-to-date for this student and term
            $this->upsertRegistration($studentId, $term, [
                'year_level'   => $yearLevel,
                'student_type' => $studentType,
                'current_program' => $user->intProgramID,
                'current_curriculum' => $user->intCurriculumID,
                'enlisted_by' => $actorId
            ]);

            foreach ($operations as $op) {
                $type = $op['type'] ?? null;

                if ($type === 'add') {
                    $classlistId = (int) ($op['classlist_id'] ?? 0);
                    $res = $this->opAdd($studentId, $term, $classlistId, $actorId, $request, $plannedAddClasslistIds);
                    $okAll = $okAll && ($res['ok'] ?? false);
                    $results[] = array_merge(['type' => 'add'], $res);
                } elseif ($type === 'drop') {
                    $classlistId = (int) ($op['classlist_id'] ?? 0);
                    $res = $this->opDrop($studentId, $term, $classlistId, $request);
                    $okAll = $okAll && ($res['ok'] ?? false);
                    $results[] = array_merge(['type' => 'drop'], $res);
                } elseif ($type === 'change_section') {
                    $fromId = (int) ($op['from_classlist_id'] ?? 0);
                    $toId   = (int) ($op['to_classlist_id'] ?? 0);
                    $res = $this->opChangeSection($studentId, $term, $fromId, $toId, $actorId, $request, $plannedAddClasslistIds);
                    $okAll = $okAll && ($res['ok'] ?? false);
                    $results[] = array_merge(['type' => 'change_section'], $res);
                } else {
                    $okAll = false;
                    $results[] = [
                        'type'    => $type,
                        'ok'      => false,
                        'message' => 'Unsupported operation type',
                    ];
                }
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('EnlistmentService::enlist failed', ['error' => $e->getMessage()]);
            return [
                'success'    => false,
                'message'    => 'Transaction failed: ' . $e->getMessage(),
                'operations' => $results,
                'current'    => $this->currentEnlisted($studentId, $term),
            ];
        }

        return [
            'success'    => $okAll,
            'message'    => $okAll ? 'OK' : 'Completed with errors',
            'operations' => $results,
            'current'    => $this->currentEnlisted($studentId, $term),
        ];
    }

    /**
     * Upsert tb_mas_registration row for the student and term with enlisted defaults (intROG=0).
     * Attempts to satisfy non-null columns observed in legacy schema.
     */
    protected function upsertRegistration(int $studentId, int $term, array $meta): void
    {
        $existing = DB::table('tb_mas_registration')
            ->where('intStudentID', $studentId)
            ->where('intAYID', $term)
            ->orderByDesc('dteRegistered')
            ->first();

        $now = now()->toDateTimeString();
        $base = [
            'intStudentID'    => $studentId,
            'intAYID'         => $term,
            'intROG'          => 0, // enlisted
            'dteRegistered'   => $now,
            'enumStudentType' => $meta['student_type'] ?? 'continuing',
            'intYearLevel'    => $meta['year_level'] ?? 1,
            'current_program'    => $meta['current_program'],
            'current_curriculum'    => $meta['current_curriculum'],
            'enlisted_by'     => $meta['enlisted_by']
        ];

        // Non-null columns observed in logs (best-effort; ignore errors if columns missing)
        $extras = [
            'loa_remarks'       => '',
            'withdrawal_period' => 0,
        ];

        if ($existing) {
            $update = array_merge(
                $base,
                // Preserve existing dteRegistered if already present
                ['dteRegistered' => $existing->dteRegistered ?: $now]
            );
            try {
                DB::table('tb_mas_registration')
                    ->where('intRegistrationID', $existing->intRegistrationID)
                    ->update(array_merge($update, $extras));
            } catch (Throwable $e) {
                // Retry without extras if schema differs
                DB::table('tb_mas_registration')
                    ->where('intRegistrationID', $existing->intRegistrationID)
                    ->update($update);
            }
        } else {
            try {
                DB::table('tb_mas_registration')->insert(array_merge($base, $extras));
            } catch (Throwable $e) {
                DB::table('tb_mas_registration')->insert($base);
            }
        }
    }

    /**
     * Add operation: insert tb_mas_classlist_student with defaults.
     */
    protected function opAdd(int $studentId, int $term, int $classlistId, ?int $actorId, Request $request, array $plannedAddClasslistIds = []): array
    {
        if ($classlistId <= 0) {
            return ['ok' => false, 'message' => 'Invalid classlist id'];
        }

        $classlist = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
        if (!$classlist) {
            return ['ok' => false, 'message' => 'Classlist not found'];
        }

        // Validate classlist term matches payload term
        $clTerm = (int) ($classlist->strAcademicYear ?? 0);
        if ($clTerm !== $term) {
            return ['ok' => false, 'message' => 'Classlist term does not match requested term'];
        }

        // Prevent duplicates
        $exists = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', $studentId)
            ->where('intClassListID', $classlistId)
            ->exists();

        if ($exists) {
            return ['ok' => false, 'message' => 'Student already enlisted in this classlist'];
        }

        // Check prerequisites
        $prerequisiteCheck = $this->prerequisiteService->checkPrerequisitesForClasslist($studentId, $classlistId);
        if (!$prerequisiteCheck['passed']) {
            $missingCount = count($prerequisiteCheck['missing_prerequisites']);
            $missingCodes = array_column($prerequisiteCheck['missing_prerequisites'], 'code');
            
            return [
                'ok' => false, 
                'message' => "Prerequisites not satisfied. Missing: " . implode(', ', $missingCodes),
                'prerequisite_check' => $prerequisiteCheck
            ];
        }

        // Check corequisites (already passed OR concurrently planned)
        $corequisiteCheck = $this->corequisiteService->checkCorequisitesForClasslist($studentId, $classlistId, $plannedAddClasslistIds);
        if (!$corequisiteCheck['passed']) {
            $missingCodes = array_column($corequisiteCheck['missing_corequisites'], 'code');
            return [
                'ok' => false,
                'message' => "Corequisites not satisfied. Missing: " . implode(', ', $missingCodes),
                'corequisite_check' => $corequisiteCheck,
                'prerequisite_check' => $prerequisiteCheck
            ];
        }

        $units = $this->getSubjectUnitsByClasslist($classlistId);

        $row = [
            'intStudentID'     => $studentId,
            'intClassListID'   => $classlistId,
            'intsyID'          => $term,
            'enumStatus'       => 'act',
            'strRemarks'       => '',
            'strUnits'         => $units !== null ? (string) $units : null,
            'enlisted_user'    => $actorId,
            // grades untouched (null/0) - rely on DB defaults
        ];

        $id = DB::table('tb_mas_classlist_student')->insertGetId($row);
        // Log create
        try {
            SystemLogService::log('create', 'ClasslistStudent', $id, null, array_merge(['intCSID' => $id], $row), $request);
        } catch (Throwable $e) {
            // ignore logging failures
        }

        return [
            'ok' => true, 
            'details' => ['intCSID' => $id, 'classlist_id' => $classlistId],
            'prerequisite_check' => $prerequisiteCheck
        ];
    }

    /**
     * Drop operation: delete tb_mas_classlist_student.
     */
    protected function opDrop(int $studentId, int $term, int $classlistId, Request $request): array
    {
        if ($classlistId <= 0) {
            return ['ok' => false, 'message' => 'Invalid classlist id'];
        }

        $existing = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', $studentId)
            ->where('intClassListID', $classlistId)
            ->first();

        if (!$existing) {
            return ['ok' => false, 'message' => 'No enlistment found to drop'];
        }

        // Log delete with old payload
        try {
            SystemLogService::log('delete', 'ClasslistStudent', (int)$existing->intCSID, (array) $existing, null, $request);
        } catch (Throwable $e) {
        }

        DB::table('tb_mas_classlist_student')
            ->where('intCSID', $existing->intCSID)
            ->delete();

        return ['ok' => true, 'details' => ['deleted' => 1]];
    }

    /**
     * Change section: drop from_classlist_id, add to_classlist_id (same term), atomically.
     */
    protected function opChangeSection(int $studentId, int $term, int $fromId, int $toId, ?int $actorId, Request $request, array $plannedAddClasslistIds = []): array
    {
        if ($fromId <= 0 || $toId <= 0) {
            return ['ok' => false, 'message' => 'Invalid classlist ids'];
        }
        if ($fromId === $toId) {
            return ['ok' => false, 'message' => 'From/To classlist ids must differ'];
        }

        // Validate existence of "from"
        $from = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', $studentId)
            ->where('intClassListID', $fromId)
            ->first();

        if (!$from) {
            return ['ok' => false, 'message' => 'Student is not enlisted in the source section'];
        }

        // Validate target classlist and term match
        $toClasslist = DB::table('tb_mas_classlist')->where('intID', $toId)->first();
        if (!$toClasslist) {
            return ['ok' => false, 'message' => 'Target classlist not found'];
        }
        $clTerm = (int) ($toClasslist->strAcademicYear ?? 0);
        if ($clTerm !== $term) {
            return ['ok' => false, 'message' => 'Target classlist term does not match requested term'];
        }

        // Prevent duplicates to target
        $existsTo = DB::table('tb_mas_classlist_student')
            ->where('intStudentID', $studentId)
            ->where('intClassListID', $toId)
            ->exists();

        if ($existsTo) {
            return ['ok' => false, 'message' => 'Student already enlisted in the target section'];
        }

        // Validate corequisites for target (consider planned concurrent adds)
        $corequisiteCheck = $this->corequisiteService->checkCorequisitesForClasslist($studentId, $toId, $plannedAddClasslistIds);
        if (!$corequisiteCheck['passed']) {
            $missingCodes = array_column($corequisiteCheck['missing_corequisites'], 'code');
            return [
                'ok' => false,
                'message' => "Corequisites not satisfied. Missing: " . implode(', ', $missingCodes),
                'corequisite_check' => $corequisiteCheck
            ];
        }

        // Perform change atomically
        // Log delete(old) then create(new)
        try {
            SystemLogService::log('delete', 'ClasslistStudent', (int)$from->intCSID, (array) $from, null, $request);
        } catch (Throwable $e) {
        }

        DB::table('tb_mas_classlist_student')->where('intCSID', $from->intCSID)->delete();

        $units = $this->getSubjectUnitsByClasslist($toId);
        $row = [
            'intStudentID'     => $studentId,
            'intClassListID'   => $toId,
            'intsyID'          => $term,
            'enumStatus'       => 'act',
            'strRemarks'       => '',
            'strUnits'         => $units !== null ? (string) $units : null,
            'enlisted_user'    => $actorId,
        ];

        $newId = DB::table('tb_mas_classlist_student')->insertGetId($row);

        try {
            SystemLogService::log('create', 'ClasslistStudent', $newId, null, array_merge(['intCSID' => $newId], $row), $request);
        } catch (Throwable $e) {
        }

        return ['ok' => true, 'details' => ['from_deleted' => 1, 'to_intCSID' => $newId]];
    }

    /**
     * Get subject units via classlist -> subject join.
     */
    protected function getSubjectUnitsByClasslist(int $classlistId): ?int
    {
        $row = DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('cl.intID', $classlistId)
            ->select('s.strUnits')
            ->first();

        if (!$row) {
            return null;
        }

        // strUnits is stored as string; normalize to int if numeric
        $u = $row->strUnits;
        if ($u === null || $u === '') {
            return null;
        }
        return is_numeric($u) ? (int) $u : null;
    }

    /**
     * Reset registration: delete classlist enrollments and registration rows for a student and term, atomically.
     */
    public function resetRegistration(int $studentId, int $term, Request $request): array
    {        
        DB::beginTransaction();
        try {                        

            // Delete enlisted classlist rows scoped to the specified term
            $clsDeleted = DB::table('tb_mas_classlist_student')
                ->where('intsyID', $term)                
                ->where('intStudentID', $studentId)
                ->delete();

            // Delete registration rows for the specified term
            $regDeleted = DB::table('tb_mas_registration')
                ->where('intStudentID', $studentId)
                ->where('intAYID', $term)
                ->delete();

            // Single summary system log entry
            try {
                SystemLogService::log('delete', 'RegistrationReset', null, null, [
                    'student_id' => $studentId,
                    'term' => $term,
                    'deleted' => [
                        'classlist_student_rows' => $clsDeleted,
                        'registrations' => $regDeleted,
                    ],
                ], $request);
            } catch (Throwable $e) {
                // ignore logging failures
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Reset completed',
                'data' => [
                    'student_id' => $studentId,
                    'term' => $term,
                    'deleted' => [
                        'classlist_student_rows' => $clsDeleted,
                        'registrations' => $regDeleted,
                    ],
                ],
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('EnlistmentService::resetRegistration failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Reset failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Snapshot of current enlisted records for student/term (minimal fields for UI refresh).
     */
    protected function currentEnlisted(int $studentId, int $term): array
    {
        $rows = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('cls.intStudentID', $studentId)
            ->where('cl.strAcademicYear', $term)
            ->select(
                'cl.intID as classlist_id',
                's.strCode as code',
                's.strDescription as description',
                's.strUnits as units'
            )
            ->orderBy('s.strCode', 'asc')
            ->get();

        return $rows->map(function ($r) {
            return [
                'classlist_id' => (int) $r->classlist_id,
                'code'         => $r->code,
                'description'  => $r->description,
                'units'        => $r->units !== null && is_numeric($r->units) ? (int) $r->units : null,
            ];
        })->toArray();
    }
}
