<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use RuntimeException;

class ClasslistAttendanceService
{
    /**
     * Ensure classlist exists and return row.
     */
    protected function getClasslist(int $classlistId): ?object
    {
        return DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
    }

    /**
     * List attendance dates for a classlist with summary counts.
     * Returns array of:
     * [
     *   { id, attendance_date, present_count, absent_count, unset_count }
     * ]
     */
    public function listDates(int $classlistId): array
    {
        // Fetch dates
        $dates = DB::table('tb_mas_classlist_attendance_date')
            ->where('intClassListID', $classlistId)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('period', 'asc')
            ->get();

        if ($dates->isEmpty()) {
            return [];
        }

        $dateIds = $dates->pluck('intID')->all();

        // Aggregate counts by date and is_present
        $counts = DB::table('tb_mas_classlist_attendance')
            ->select('intAttendanceDateID', 'is_present', DB::raw('count(*) as cnt'))
            ->whereIn('intAttendanceDateID', $dateIds)
            ->groupBy('intAttendanceDateID', 'is_present')
            ->get();

        $byDate = [];
        foreach ($counts as $r) {
            $did = (int) $r->intAttendanceDateID;
            $byDate[$did] = $byDate[$did] ?? ['present' => 0, 'absent' => 0, 'unset' => 0];
            // is_present can be null/0/1
            if ($r->is_present === null) {
                $byDate[$did]['unset'] += (int) $r->cnt;
            } elseif ((int) $r->is_present === 1) {
                $byDate[$did]['present'] += (int) $r->cnt;
            } else {
                $byDate[$did]['absent'] += (int) $r->cnt;
            }
        }

        $out = [];
        foreach ($dates as $d) {
            $agg = $byDate[(int) $d->intID] ?? ['present' => 0, 'absent' => 0, 'unset' => 0];
            $out[] = [
                'id' => (int) $d->intID,
                'attendance_date' => (string) $d->attendance_date,
                'period' => isset($d->period) ? (string)$d->period : null,
                'present_count' => $agg['present'],
                'absent_count'  => $agg['absent'],
                'unset_count'   => $agg['unset'],
            ];
        }

        return $out;
    }

    /**
     * Idempotently create an attendance date for a classlist and seed rows for all enrolled students with is_present=null.
     * Returns array: { id, attendance_date, seeded: int }
     */
    public function createDate(int $classlistId, string $date, string $period, ?int $actorId = null): array
    {
        $cl = $this->getClasslist($classlistId);
        if (!$cl) {
            throw new RuntimeException('Classlist not found');
        }
        if ((int) ($cl->isDissolved ?? 0) === 1) {
            throw new RuntimeException('Cannot create attendance date for a dissolved classlist');
        }

        // Normalize period
        $p = strtolower(trim((string)$period));
        if (!in_array($p, ['midterm', 'finals'], true)) {
            throw new RuntimeException('Invalid attendance period; must be midterm or finals');
        }

        // Create or fetch date row (classlist + date + period)
        $row = DB::table('tb_mas_classlist_attendance_date')
            ->where('intClassListID', $classlistId)
            ->where('attendance_date', $date)
            ->where('period', $p)
            ->first();

        if (!$row) {
            $id = (int) DB::table('tb_mas_classlist_attendance_date')->insertGetId([
                'intClassListID'  => $classlistId,
                'attendance_date' => $date,
                'period'          => $p,
                'created_by'      => $actorId,
                'created_at'      => Date::now(),
            ]);
        } else {
            $id = (int) $row->intID;
        }

        // Seed rows for all enrolled students (tb_mas_classlist_student)
        $existing = DB::table('tb_mas_classlist_attendance')
            ->where('intAttendanceDateID', $id)
            ->pluck('intCSID')
            ->all();
        $existingMap = array_fill_keys(array_map('intval', $existing), true);

        $seeded = 0;
        $chunkSize = 500;

        DB::table('tb_mas_classlist_student')
            ->where('intClassListID', $classlistId)
            ->orderBy('intCSID')
            ->chunk($chunkSize, function ($rows) use ($id, $classlistId, &$seeded, $existingMap) {
                $ins = [];
                foreach ($rows as $r) {
                    $csid = (int) $r->intCSID;
                    if (isset($existingMap[$csid])) {
                        continue;
                    }
                    $ins[] = [
                        'intAttendanceDateID' => $id,
                        'intClassListID'      => $classlistId,
                        'intCSID'             => $csid,
                        'intStudentID'        => (int) ($r->intStudentID ?? 0),
                        'is_present'          => null,
                        'remarks'             => null,
                        'marked_by'           => null,
                        'marked_at'           => null,
                    ];
                }
                if (!empty($ins)) {
                    // Bulk insert
                    DB::table('tb_mas_classlist_attendance')->insert($ins);
                    $seeded += count($ins);
                }
            });

        return [
            'id' => $id,
            'attendance_date' => $date,
            'period' => $p,
            'seeded' => $seeded,
        ];
    }

    /**
     * Get date details including rows with student info.
     * Returns array:
     * {
     *   classlist_id, date: { id, attendance_date },
     *   students: [ { intCSID, intStudentID, strStudentNumber, strLastname, strFirstname, strMiddlename, is_present, remarks } ]
     * }
     */
    public function getDateDetails(int $classlistId, int $dateId): array
    {
        // Verify date belongs to classlist
        $date = DB::table('tb_mas_classlist_attendance_date')
            ->where('intID', $dateId)
            ->where('intClassListID', $classlistId)
            ->first();

        if (!$date) {
            throw new RuntimeException('Attendance date not found');
        }

        $rows = DB::table('tb_mas_classlist_attendance as a')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'a.intStudentID')
            ->select(
                'a.intID',
                'a.intCSID',
                'a.intStudentID',
                'a.is_present',
                'a.remarks',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname',
                'u.strMiddlename'
            )
            ->where('a.intAttendanceDateID', $dateId)
            ->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->get()
            ->map(function ($r) {
                return [
                    'intID'           => (int) $r->intID,
                    'intCSID'         => (int) $r->intCSID,
                    'intStudentID'    => (int) $r->intStudentID,
                    'is_present'      => $r->is_present === null ? null : (bool) $r->is_present,
                    'remarks'         => $r->remarks,
                    'strStudentNumber'=> $r->strStudentNumber,
                    'strLastname'     => $r->strLastname,
                    'strFirstname'    => $r->strFirstname,
                    'strMiddlename'   => $r->strMiddlename,
                ];
            })
            ->all();

        return [
            'classlist_id' => $classlistId,
            'date' => [
                'id' => (int) $date->intID,
                'attendance_date' => (string) $date->attendance_date,
                'period' => isset($date->period) ? (string)$date->period : null,
            ],
            'students' => $rows,
        ];
    }

    /**
     * Bulk save marks for a given date.
     * $items: array of { intCSID: int, is_present: bool|null, remarks?: string|null }
     * Returns: { updated: int }
     */
    public function saveMarks(int $classlistId, int $dateId, array $items, ?int $actorId = null): array
    {
        // Verify date belongs to classlist
        $date = DB::table('tb_mas_classlist_attendance_date')
            ->where('intID', $dateId)
            ->where('intClassListID', $classlistId)
            ->first();

        if (!$date) {
            throw new RuntimeException('Attendance date not found');
        }

        // Build valid CSIDs for this date
        $validRows = DB::table('tb_mas_classlist_attendance')
            ->where('intAttendanceDateID', $dateId)
            ->select('intCSID')
            ->pluck('intCSID')
            ->all();

        $validMap = array_fill_keys(array_map('intval', $validRows), true);

        $updated = 0;
        $now = Date::now();

        foreach ($items as $it) {
            $csid = (int) Arr::get($it, 'intCSID', 0);
            if ($csid <= 0 || !isset($validMap[$csid])) {
                // skip invalid
                continue;
            }

            $isPresent = Arr::exists($it, 'is_present') ? Arr::get($it, 'is_present') : null;
            if ($isPresent === '' || $isPresent === 'null') {
                $isPresent = null;
            }
            if ($isPresent !== null) {
                // normalize to boolean
                $isPresent = (bool) (is_string($isPresent) ? ($isPresent === '1' || strtolower($isPresent) === 'true') : $isPresent);
            }

            $remarks = Arr::get($it, 'remarks', null);
            if ($isPresent === true) {
                // clear remarks when marked present
                $remarks = null;
            } elseif ($isPresent === null) {
                // unset state - clear remarks as well
                $remarks = null;
            } else {
                // absent: allow remarks up to 255; DB will enforce length
                if ($remarks !== null) {
                    $remarks = trim((string) $remarks);
                    if ($remarks === '') {
                        $remarks = null;
                    }
                }
            }

            $data = [
                'is_present' => $isPresent,
                'remarks'    => $remarks,
                'marked_by'  => $actorId,
                'marked_at'  => $now,
            ];

            $aff = DB::table('tb_mas_classlist_attendance')
                ->where('intAttendanceDateID', $dateId)
                ->where('intCSID', $csid)
                ->update($data);

            $updated += $aff;
        }

        return ['updated' => $updated];
    }
}
