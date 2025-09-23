<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\StudentAdvisor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StudentAdvisorService
{
    /**
     * Bulk-assign advisor to students.
     *
     * @param int        $advisorId
     * @param array      $studentIds
     * @param array      $studentNumbers
     * @param bool       $replaceExisting
     * @param int        $actorFacultyId
     * @param int|null   $campusId         Override campus to store (e.g. globally selected campus). When null, falls back to overlap campus.
     * @return array{advisor: array|null, results: array<int, array{student_id:int|null, ok:bool, message:string}>}
     */
    public function assignBulk(
        int $advisorId,
        array $studentIds = [],
        array $studentNumbers = [],
        bool $replaceExisting = false,
        int $actorFacultyId = 0,
        ?int $campusId = null
    ): array {
        $advisor = DB::table('tb_mas_faculty')->where('intID', $advisorId)->first();
        if (!$advisor) {
            return [
                'advisor' => null,
                'results' => [
                    ['student_id' => null, 'ok' => false, 'message' => 'Advisor not found'],
                ],
            ];
        }
        if ((int) ($advisor->teaching ?? 0) !== 1) {
            return [
                'advisor' => null,
                'results' => [
                    ['student_id' => null, 'ok' => false, 'message' => 'Advisor is not marked as teaching'],
                ],
            ];
        }

        // Authorization: actor must share department & campus with advisor
        $overlap = $this->checkDeptCampusOverlap($actorFacultyId, $advisorId);
        if (!$overlap['ok']) {
            return [
                'advisor' => null,
                'results' => [
                    ['student_id' => null, 'ok' => false, 'message' => $overlap['message'] ?? 'Unauthorized: no department/campus overlap'],
                ],
            ];
        }

        // Campus to record on the assignment: prefer explicit override from caller, else overlap campus
        $campToUse = $campusId !== null ? (int) $campusId : ($overlap['campus_id'] ?? null);

        // Resolve student ids (merge unique)
        $resolvedIds = $this->resolveStudentIds($studentIds, $studentNumbers);
        if (empty($resolvedIds)) {
            return [
                'advisor' => [
                    'id' => (int) $advisorId,
                    'name' => trim(($advisor->strFirstname ?? '') . ' ' . ($advisor->strLastname ?? '')),
                ],
                'results' => [
                    ['student_id' => null, 'ok' => false, 'message' => 'No students resolved from inputs'],
                ],
            ];
        }

        $results = [];
        $now = Carbon::now();

        // Chunk operations to avoid long-running transactions
        foreach (array_chunk($resolvedIds, 100) as $chunk) {
            DB::transaction(function () use ($chunk, $advisorId, $actorFacultyId, $replaceExisting, $overlap, $campToUse, $now, &$results) {
                foreach ($chunk as $sid) {
                    // Ensure student exists
                    $studentExists = DB::table('tb_mas_users')->where('intID', $sid)->exists();
                    if (!$studentExists) {
                        $results[] = ['student_id' => (int) $sid, 'ok' => false, 'message' => 'Student not found'];
                        continue;
                    }

                    // Check active assignment
                    $active = StudentAdvisor::query()
                        ->where('intStudentID', $sid)
                        ->where('is_active', 1)
                        ->first();

                    if ($active) {
                        if (!$replaceExisting) {
                            $results[] = ['student_id' => (int) $sid, 'ok' => false, 'message' => 'Student already has an active advisor'];
                            continue;
                        }
                        // End the active assignment
                        $this->endActiveAssignment($sid, $now);
                    }

                    // Create new assignment
                    $this->createAssignment(
                        $sid,
                        $advisorId,
                        $actorFacultyId,
                        $overlap['department_code'] ?? null,
                        $campToUse,
                        $now
                    );

                    // Update denormalized pointer on users
                    DB::table('tb_mas_users')
                        ->where('intID', $sid)
                        ->update(['intAdvisorID' => $advisorId]);

                    $results[] = ['student_id' => (int) $sid, 'ok' => true, 'message' => 'Assigned'];
                }
            });
        }

        return [
            'advisor' => [
                'id' => (int) $advisorId,
                'name' => trim(($advisor->strFirstname ?? '') . ' ' . ($advisor->strLastname ?? '')),
            ],
            'results' => $results,
        ];
    }

    /**
     * Switch all active advisees from one advisor to another.
     *
     * @return array{from_advisor_id:int, to_advisor_id:int, total_processed:int, switched:int, skipped:int, errors:array<int, array{student_id:int, message:string}>}
     */
    public function switchAll(int $fromAdvisorId, int $toAdvisorId, int $actorFacultyId): array
    {
        if ($fromAdvisorId === $toAdvisorId) {
            return [
                'from_advisor_id' => $fromAdvisorId,
                'to_advisor_id' => $toAdvisorId,
                'total_processed' => 0,
                'switched' => 0,
                'skipped' => 0,
                'errors' => [
                    ['student_id' => 0, 'message' => 'from_advisor_id and to_advisor_id must differ'],
                ],
            ];
        }

        $toAdvisor = DB::table('tb_mas_faculty')->where('intID', $toAdvisorId)->first();
        if (!$toAdvisor) {
            return [
                'from_advisor_id' => $fromAdvisorId,
                'to_advisor_id' => $toAdvisorId,
                'total_processed' => 0,
                'switched' => 0,
                'skipped' => 0,
                'errors' => [
                    ['student_id' => 0, 'message' => 'Target advisor not found'],
                ],
            ];
        }
        if ((int) ($toAdvisor->teaching ?? 0) !== 1) {
            return [
                'from_advisor_id' => $fromAdvisorId,
                'to_advisor_id' => $toAdvisorId,
                'total_processed' => 0,
                'switched' => 0,
                'skipped' => 0,
                'errors' => [
                    ['student_id' => 0, 'message' => 'Target advisor is not marked as teaching'],
                ],
            ];
        }

        // Authorization: actor must share department & campus with TO advisor (stricter check)
        $overlap = $this->checkDeptCampusOverlap($actorFacultyId, $toAdvisorId);
        if (!$overlap['ok']) {
            return [
                'from_advisor_id' => $fromAdvisorId,
                'to_advisor_id' => $toAdvisorId,
                'total_processed' => 0,
                'switched' => 0,
                'skipped' => 0,
                'errors' => [
                    ['student_id' => 0, 'message' => $overlap['message'] ?? 'Unauthorized: no department/campus overlap with target advisor'],
                ],
            ];
        }

        $rows = StudentAdvisor::query()
            ->where('intAdvisorID', $fromAdvisorId)
            ->where('is_active', 1)
            ->get(['intStudentID']);

        $total = $rows->count();
        $switched = 0;
        $skipped = 0;
        $errors = [];
        $now = Carbon::now();

        foreach ($rows->chunk(100) as $chunk) {
            DB::transaction(function () use ($chunk, $fromAdvisorId, $toAdvisorId, $overlap, $now, &$switched, &$skipped, &$errors) {
                foreach ($chunk as $r) {
                    $sid = (int) ($r->intStudentID ?? 0);
                    if ($sid <= 0) {
                        $skipped++;
                        $errors[] = ['student_id' => 0, 'message' => 'Invalid student id in source assignment'];
                        continue;
                    }

                    try {
                        // End existing active (by definition one active per student)
                        $this->endActiveAssignment($sid, $now);

                        // Create new assignment under target advisor
                        $this->createAssignment(
                            $sid,
                            $toAdvisorId,
                            0, // assigned_by unknown for switch-all when automated; could be set to actor if preferred
                            $overlap['department_code'] ?? null,
                            $overlap['campus_id'] ?? null,
                            $now
                        );

                        // Update denormalized pointer
                        DB::table('tb_mas_users')
                            ->where('intID', $sid)
                            ->update(['intAdvisorID' => $toAdvisorId]);

                        $switched++;
                    } catch (\Throwable $e) {
                        $skipped++;
                        $errors[] = ['student_id' => $sid, 'message' => 'Switch failed: ' . $e->getMessage()];
                    }
                }
            });
        }

        return [
            'from_advisor_id' => $fromAdvisorId,
            'to_advisor_id'   => $toAdvisorId,
            'total_processed' => $total,
            'switched'        => $switched,
            'skipped'         => $skipped,
            'errors'          => $errors,
        ];
    }

    /**
     * Return current advisor and full history for a given student.
     *
     * @return array{student: array|null, current: array|null, history: array<int, array>}
     */
    public function showByStudent(?int $studentId = null, ?string $studentNumber = null): array
    {
        $student = null;

        if ($studentId !== null) {
            $student = DB::table('tb_mas_users')->where('intID', $studentId)->first();
        } elseif ($studentNumber !== null) {
            $student = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        }

        if (!$student) {
            return [
                'student' => null,
                'current' => null,
                'history' => [],
            ];
        }

        $sid = (int) $student->intID;

        $current = StudentAdvisor::query()
            ->where('intStudentID', $sid)
            ->where('is_active', 1)
            ->orderByDesc('started_at')
            ->first();

        $history = StudentAdvisor::query()
            ->where('intStudentID', $sid)
            ->orderByDesc('started_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id'               => (int) $r->intID,
                    'student_id'       => (int) $r->intStudentID,
                    'advisor_id'       => (int) $r->intAdvisorID,
                    'is_active'        => (int) $r->is_active,
                    'started_at'       => optional($r->started_at)->toDateTimeString(),
                    'ended_at'         => $r->ended_at ? $r->ended_at->toDateTimeString() : null,
                    'assigned_by'      => $r->assigned_by !== null ? (int) $r->assigned_by : null,
                    'department_code'  => $r->department_code !== null ? (string) $r->department_code : null,
                    'campus_id'        => $r->campus_id !== null ? (int) $r->campus_id : null,
                ];
            })
            ->toArray();

        // Build advisor name map for current/history/student pointers
        $advisorIds = [];
        if ($current && isset($current->intAdvisorID)) { $advisorIds[] = (int) $current->intAdvisorID; }
        foreach ($history as $h) {
            if (isset($h['advisor_id'])) { $advisorIds[] = (int) $h['advisor_id']; }
        }
        if (isset($student->intAdvisorID) && $student->intAdvisorID) { $advisorIds[] = (int) $student->intAdvisorID; }
        $advisorIds = array_values(array_unique(array_filter(array_map('intval', $advisorIds), function ($v) { return $v > 0; })));

        $advisorNameMap = [];
        if (!empty($advisorIds)) {
            $facRows = DB::table('tb_mas_faculty')->whereIn('intID', $advisorIds)->get(['intID', 'strFirstname', 'strLastname']);
            foreach ($facRows as $fr) {
                $advisorNameMap[(int) ($fr->intID ?? 0)] = trim(($fr->strFirstname ?? '') . ' ' . ($fr->strLastname ?? ''));
            }
        }

        // Build campus name map for current/history
        $campusIds = [];
        foreach ($history as $h) {
            if (isset($h['campus_id']) && $h['campus_id'] !== null) {
                $campusIds[] = (int) $h['campus_id'];
            }
        }
        if ($current && isset($current->campus_id) && $current->campus_id !== null) {
            $campusIds[] = (int) $current->campus_id;
        }
        $campusIds = array_values(array_unique(array_filter(array_map('intval', $campusIds), function ($v) { return $v > 0; })));

        $campusNameMap = [];
        if (!empty($campusIds)) {
            $campRows = DB::table('tb_mas_campuses')->whereIn('id', $campusIds)->get(['id', 'campus_name']);
            foreach ($campRows as $cr) {
                $campusNameMap[(int) ($cr->id ?? 0)] = (string) ($cr->campus_name ?? '');
            }
        }

        // Enrich history with advisor_name and campus_name
        foreach ($history as &$row) {
            $aid = isset($row['advisor_id']) ? (int) $row['advisor_id'] : 0;
            $row['advisor_name'] = ($aid > 0 && isset($advisorNameMap[$aid])) ? $advisorNameMap[$aid] : null;

            $cid = isset($row['campus_id']) ? (int) $row['campus_id'] : 0;
            $row['campus_name'] = ($cid > 0 && isset($campusNameMap[$cid])) ? $campusNameMap[$cid] : null;
        }
        unset($row);

        $cur = $current ? [
            'id'              => (int) $current->intID,
            'student_id'      => (int) $current->intStudentID,
            'advisor_id'      => (int) $current->intAdvisorID,
            'advisor_name'    => isset($advisorNameMap[(int) $current->intAdvisorID]) ? $advisorNameMap[(int) $current->intAdvisorID] : null,
            'started_at'      => optional($current->started_at)->toDateTimeString(),
            'department_code' => $current->department_code !== null ? (string) $current->department_code : null,
            'campus_id'       => $current->campus_id !== null ? (int) $current->campus_id : null,
            'campus_name'     => (isset($current->campus_id) && $current->campus_id !== null && isset($campusNameMap[(int) $current->campus_id]))
                                   ? $campusNameMap[(int) $current->campus_id]
                                   : null,
        ] : null;

        $studentCurrentAdvisorName = null;
        $__ptrId = ($cur && is_array($cur) && isset($cur['advisor_id'])) ? (int) $cur['advisor_id'] : (isset($student->intAdvisorID) ? (int) $student->intAdvisorID : 0);
        if ($__ptrId > 0 && isset($advisorNameMap[$__ptrId])) {
            $studentCurrentAdvisorName = $advisorNameMap[$__ptrId];
        }

        return [
            'student' => [
                'id'                   => $sid,
                'student_number'       => (string) ($student->strStudentNumber ?? ''),
                'first_name'           => (string) ($student->strFirstname ?? ''),
                'last_name'            => (string) ($student->strLastname ?? ''),
                'intAdvisorID'         => isset($student->intAdvisorID) ? (int) $student->intAdvisorID : null,
                'current_advisor_name' => $studentCurrentAdvisorName,
            ],
            'current' => $cur,
            'history' => $history,
        ];
    }

    /**
     * Unassign current advisor for a student (if any).
     *
     * @return array{ok:bool, message:string}
     */
    public function unassign(int $studentId, int $actorFacultyId = 0): array
    {
        $exists = DB::table('tb_mas_users')->where('intID', $studentId)->exists();
        if (!$exists) {
            return ['ok' => false, 'message' => 'Student not found'];
        }

        $active = StudentAdvisor::query()
            ->where('intStudentID', $studentId)
            ->where('is_active', 1)
            ->first();

        if (!$active) {
            // Idempotent
            DB::table('tb_mas_users')->where('intID', $studentId)->update(['intAdvisorID' => null]);
            return ['ok' => true, 'message' => 'No active advisor (noop)'];
        }

        DB::transaction(function () use ($studentId) {
            $this->endActiveAssignment($studentId, Carbon::now());
            DB::table('tb_mas_users')->where('intID', $studentId)->update(['intAdvisorID' => null]);
        });

        return ['ok' => true, 'message' => 'Unassigned'];
    }

    /**
     * Resolve student IDs from a combination of raw IDs and student numbers.
     *
     * @param array<int|string> $ids
     * @param array<string>     $studentNumbers
     * @return array<int>
     */
    private function resolveStudentIds(array $ids, array $studentNumbers): array
    {
        $out = [];

        // Coerce numeric ids
        foreach ($ids as $v) {
            if (is_numeric($v)) {
                $n = (int) $v;
                if ($n > 0) {
                    $out[$n] = true;
                }
            }
        }

        if (!empty($studentNumbers)) {
            $sns = array_values(array_filter(array_map(function ($s) {
                return trim((string) $s);
            }, $studentNumbers), function ($s) {
                return $s !== '';
            }));

            if (!empty($sns)) {
                $rows = DB::table('tb_mas_users')
                    ->whereIn('strStudentNumber', $sns)
                    ->get(['intID']);

                foreach ($rows as $r) {
                    $n = (int) ($r->intID ?? 0);
                    if ($n > 0) {
                        $out[$n] = true;
                    }
                }
            }
        }

        return array_map('intval', array_keys($out));
    }

    /**
     * End any active advisor assignment for a student.
     */
    private function endActiveAssignment(int $studentId, ?Carbon $when = null): void
    {
        $when = $when ?: Carbon::now();

        StudentAdvisor::query()
            ->where('intStudentID', $studentId)
            ->where('is_active', 1)
            ->update([
                'is_active' => 0,
                'ended_at'  => $when,
                'updated_at'=> Carbon::now(),
            ]);
    }

    /**
     * Create a new active advisor assignment.
     */
    private function createAssignment(
        int $studentId,
        int $advisorId,
        int $assignedBy,
        ?string $departmentCode,
        ?int $campusId,
        ?Carbon $startedAt = null
    ): void {
        $startedAt = $startedAt ?: Carbon::now();

        StudentAdvisor::query()->create([
            'intStudentID'    => $studentId,
            'intAdvisorID'    => $advisorId,
            'is_active'       => 1,
            'started_at'      => $startedAt,
            'ended_at'        => null,
            'assigned_by'     => $assignedBy ?: null,
            'department_code' => $departmentCode !== null ? strtolower(trim($departmentCode)) : null,
            'campus_id'       => $campusId,
        ]);
    }

    /**
     * Check department+campus overlap between actor and advisor.
     * - Department must match (case-insensitive)
     * - Campus must match when both provided; null on either side is treated as global (match-any)
     *
     * @return array{ok:bool, message?:string, department_code?:string, campus_id?:int|null}
     */
    private function checkDeptCampusOverlap(int $actorFacultyId, int $advisorFacultyId): array
    {
        $actorTags = $this->facultyDeptTags($actorFacultyId);
        $advisorTags = $this->facultyDeptTags($advisorFacultyId);

        if (empty($actorTags) || empty($advisorTags)) {
            return ['ok' => false, 'message' => 'No department/campus tags to authorize action'];
        }

        foreach ($actorTags as $a) {
            $adept = strtolower(trim((string) $a['department_code']));
            $acamp = $a['campus_id'];

            foreach ($advisorTags as $b) {
                $bdept = strtolower(trim((string) $b['department_code']));
                $bcamp = $b['campus_id'];

                if ($adept !== '' && $adept === $bdept) {
                    // Campus match: exact, or either side null (treat null as global)
                    if ($acamp === $bcamp || $acamp === null || $bcamp === null) {
                        return [
                            'ok' => true,
                            'department_code' => $adept,
                            'campus_id' => $acamp !== null ? $acamp : $bcamp, // prefer concrete campus when available
                        ];
                    }
                }
            }
        }

        return ['ok' => false, 'message' => 'No overlapping department and campus tags'];
    }

    /**
     * Fetch department/campus tags for a faculty.
     *
     * @return array<int, array{department_code:string, campus_id:int|null}>
     */
    private function facultyDeptTags(int $facultyId): array
    {
        $rows = DB::table('tb_mas_faculty_departments')
            ->where('intFacultyID', $facultyId)
            ->get(['department_code', 'campus_id']);

        $out = [];
        foreach ($rows as $r) {
            $dept = strtolower(trim((string) ($r->department_code ?? '')));
            $camp = isset($r->campus_id) ? (int) $r->campus_id : null;
            if ($dept !== '') {
                $out[] = ['department_code' => $dept, 'campus_id' => $camp];
            }
        }
        return $out;
    }
}
