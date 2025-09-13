<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use App\Services\ClasslistAttendanceService;

class ClasslistAttendanceImportService
{
    private const SHEET_NAME = 'attendance';
    private const SHEET_NAME_ALL = 'attendance_all';
    private const SHEET_NAME_MATRIX = 'attendance_matrix';

    /**
     * Parse uploaded .xlsx into generator of normalized rows.
     * Returns: \Generator<['line' => int, 'data' => array]>
     * Expects a header row with, at minimum: intCSID, is_present (case-insensitive on read).
     *
     * @param string $path
     * @return \Generator
     */
    public function parseXlsx(string $path): \Generator
    {
        if (!is_file($path)) {
            throw new RuntimeException('Uploaded file not found.');
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $ss = $reader->load($path);

        $sheet = $ss->getSheetByName(self::SHEET_NAME) ?: $ss->getSheet(0);

        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = (int) $sheet->getHighestRow();

        // header map (column index -> lowercase header)
        $header = [];
        for ($c = 1; $c <= $highestCol; $c++) {
            $v = (string) $sheet->getCellByColumnAndRow($c, 1)->getValue();
            $v = trim($v);
            if ($v !== '') {
                $header[$c] = strtolower($v);
            }
        }

        for ($r = 2; $r <= $highestRow; $r++) {
            $row = [];
            for ($c = 1; $c <= $highestCol; $c++) {
                if (!isset($header[$c])) continue;
                $key = $header[$c];
                $val = $sheet->getCellByColumnAndRow($c, $r)->getFormattedValue();
                $row[$key] = is_string($val) ? trim($val) : $val;
            }
            if ($this->rowIsEmpty($row)) continue;
            yield ['line' => $r, 'data' => $row];
        }
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') return false;
        }
        return true;
    }

    /**
     * Normalize is_present tokens to true/false/null.
     * Accepted values (case-insensitive):
     *  - Present/true: "1","true","present","p","yes"
     *  - Absent/false: "0","false","absent","a","no"
     *  - Unset/null: "", "null", "unset"
     *
     * @param mixed $val
     * @return array [bool|null $value, bool $valid]
     */
    public function normalizeIsPresent($val): array
    {
        if ($val === null) {
            return [null, true];
        }
        if (is_bool($val)) {
            return [$val, true];
        }
        if (is_numeric($val)) {
            $n = (int) $val;
            if ($n === 1) return [true, true];
            if ($n === 0) return [false, true];
        }
        $s = strtolower(trim((string) $val));
        if ($s === '') return [null, true];
        $trueSet  = ['1', 'true', 'present', 'p', 'yes'];
        $falseSet = ['0', 'false', 'absent', 'a', 'no'];
        $nullSet  = ['null', 'unset'];
        if (in_array($s, $trueSet, true))  return [true, true];
        if (in_array($s, $falseSet, true)) return [false, true];
        if (in_array($s, $nullSet, true))  return [null, true];
        return [null, false];
    }

    /**
     * Perform upsert of attendance rows based on parsed rows and date context.
     *
     * @param iterable $rows generator from parseXlsx()
     * @param int $classlistId
     * @param int $dateId
     * @param int|null $actorId
     * @return array [updated, skipped, totalRows, errors[]]
     */
    public function upsert(iterable $rows, int $classlistId, int $dateId, ?int $actorId = null): array
    {
        $updated = 0; $skipped = 0;
        $errors = [];

        // Verify date belongs to classlist
        $date = DB::table('tb_mas_classlist_attendance_date')
            ->where('intID', $dateId)
            ->where('intClassListID', $classlistId)
            ->first();

        if (!$date) {
            throw new RuntimeException('Attendance date not found for this classlist.');
        }

        // Build valid CSIDs map for this date
        $validRows = DB::table('tb_mas_classlist_attendance')
            ->where('intAttendanceDateID', $dateId)
            ->select('intCSID')
            ->pluck('intCSID')
            ->all();
        $validMap = array_fill_keys(array_map('intval', $validRows), true);

        $now = Date::now();

        foreach ($rows as $item) {
            $line = (int) ($item['line'] ?? 0);
            $data = (array) ($item['data'] ?? []);

            // Extract fields (case-insensitive)
            $intCSID = $this->get($data, ['intcsid', 'intCSID', 'csid'], null);
            $isPres  = $this->get($data, ['is_present', 'ispresent'], null);
            $remarks = $this->get($data, ['remarks'], null);

            if ($intCSID === null || $intCSID === '') {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'MISSING_INTCSID', 'message' => 'Missing intCSID column.'];
                continue;
            }
            $intCSID = (int) $intCSID;
            if ($intCSID <= 0) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_INTCSID', 'message' => 'Invalid intCSID value.'];
                continue;
            }

            if (!isset($validMap[$intCSID])) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'CSID_NOT_IN_DATE', 'message' => 'intCSID not part of this attendance date.'];
                continue;
            }

            // Normalize is_present
            [$norm, $ok] = $this->normalizeIsPresent($isPres);
            if (!$ok) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_IS_PRESENT', 'message' => 'Invalid is_present value.'];
                continue;
            }

            // Remarks rules:
            // - When present (true) or unset (null): clear remarks
            // - When absent (false): trim to 255; empty -> null
            if ($norm === true || $norm === null) {
                $remarks = null;
            } else {
                if ($remarks !== null) {
                    $remarks = trim((string) $remarks);
                    if ($remarks === '') {
                        $remarks = null;
                    } elseif (mb_strlen($remarks) > 255) {
                        $remarks = mb_substr($remarks, 0, 255);
                    }
                }
            }

            $aff = DB::table('tb_mas_classlist_attendance')
                ->where('intAttendanceDateID', $dateId)
                ->where('intCSID', $intCSID)
                ->update([
                    'is_present' => $norm,
                    'remarks'    => $remarks,
                    'marked_by'  => $actorId,
                    'marked_at'  => $now,
                ]);

            if ($aff > 0) {
                $updated += $aff;
            } else {
                // No change - treat as skipped
                $skipped++;
            }
        }

        $total = $updated + $skipped;

        return [
            'updated'   => $updated,
            'skipped'   => $skipped,
            'totalRows' => $total,
            'errors'    => $errors,
        ];
    }

    private function get(array $row, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            foreach ($row as $rk => $rv) {
                if (strtolower(trim((string) $rk)) === strtolower(trim((string) $k))) {
                    return is_string($rv) ? trim($rv) : $rv;
                }
            }
        }
        return $default;
    }

    /**
     * Generic parser allowing a custom sheet name.
     *
     * @param string $path
     * @param string $sheetName
     * @return \Generator
     */
    public function parseXlsxWithSheet(string $path, string $sheetName): \Generator
    {
        if (!is_file($path)) {
            throw new RuntimeException('Uploaded file not found.');
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $ss = $reader->load($path);

        $sheet = $ss->getSheetByName($sheetName) ?: $ss->getSheet(0);

        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = (int) $sheet->getHighestRow();

        // header map (column index -> lowercase header)
        $header = [];
        for ($c = 1; $c <= $highestCol; $c++) {
            $v = (string) $sheet->getCellByColumnAndRow($c, 1)->getValue();
            $v = trim($v);
            if ($v !== '') {
                $header[$c] = strtolower($v);
            }
        }

        for ($r = 2; $r <= $highestRow; $r++) {
            $row = [];
            for ($c = 1; $c <= $highestCol; $c++) {
                if (!isset($header[$c])) continue;
                $key = $header[$c];
                $val = $sheet->getCellByColumnAndRow($c, $r)->getFormattedValue();
                $row[$key] = is_string($val) ? trim($val) : $val;
            }
            if ($this->rowIsEmpty($row)) continue;
            yield ['line' => $r, 'data' => $row];
        }
    }

    /**
     * Parse "attendance_all" sheet for all-dates import.
     *
     * @param string $path
     * @return \Generator
     */
    public function parseXlsxAll(string $path): \Generator
    {
        return $this->parseXlsxWithSheet($path, self::SHEET_NAME_ALL);
    }

    /**
     * Parse "attendance_matrix" sheet for matrix import.
     * Row 1 contains headers: A=intCSID, B=student_number, C=last_name, D=first_name, E..=dates (YYYY-MM-DD)
     * Yields: ['line' => int, 'data' => ['intCSID' => int, 'dates' => array<date(string)>=>mixed]]
     *
     * @param string $path
     * @return \Generator
     */
    public function parseXlsxMatrix(string $path): \Generator
    {
        if (!is_file($path)) {
            throw new RuntimeException('Uploaded file not found.');
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $ss = $reader->load($path);

        $sheet = $ss->getSheetByName(self::SHEET_NAME_MATRIX) ?: $ss->getSheet(0);

        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = (int) $sheet->getHighestRow();

        // Discover date headers from column E (5) onwards
        // Use getFormattedValue() so user-added date headers in Excel (which may be typed as dates) are recognized.
        $dateCols = []; // colIndex => 'YYYY-MM-DD'
        for ($c = 5; $c <= $highestCol; $c++) {
            $cell = $sheet->getCellByColumnAndRow($c, 1);
            $hdr  = $cell->getFormattedValue();
            $hdr  = is_string($hdr) ? trim($hdr) : (is_numeric($hdr) ? trim((string)$hdr) : '');
            // If formatted value is empty but the raw value is a numeric Excel serial, attempt conversion
            if ($hdr === '') {
                $raw = $cell->getValue();
                if (is_numeric($raw)) {
                    try {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw);
                        if ($dt) {
                            $hdr = $dt->format('Y-m-d');
                        }
                    } catch (\Throwable $e) {
                        // ignore; will try other normalizations below
                    }
                }
            }

            // If it's a non-empty string but not in strict YYYY-MM-DD, try to parse common user-entered formats
            if ($hdr !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hdr)) {
                try {
                    $dt = new \DateTime($hdr);
                    if ($dt) {
                        $hdr = $dt->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    // leave as-is; will fail regex below
                }
            }

            if ($hdr !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hdr)) {
                $dateCols[$c] = $hdr;
            }
        }
        if (count($dateCols) === 0) {
            throw new RuntimeException('No date headers found starting from column E. Expected YYYY-MM-DD.');
        }

        for ($r = 2; $r <= $highestRow; $r++) {
            // Read intCSID from column A (1)
            $rawCsid = $sheet->getCellByColumnAndRow(1, $r)->getFormattedValue();
            if ($rawCsid === null || $rawCsid === '') {
                // empty row - skip
                // but still check if the entire row is empty
                $allEmpty = true;
                foreach ($dateCols as $colIndex => $_d) {
                    $v = $sheet->getCellByColumnAndRow($colIndex, $r)->getFormattedValue();
                    if ($v !== null && $v !== '') {
                        $allEmpty = false; break;
                    }
                }
                if ($allEmpty) continue;
            }
            $intCSID = (int) $rawCsid;
            $rowDates = [];
            foreach ($dateCols as $colIndex => $dateYmd) {
                $val = $sheet->getCellByColumnAndRow($colIndex, $r)->getFormattedValue();
                $rowDates[$dateYmd] = is_string($val) ? trim($val) : $val;
            }
            // Skip if CSID missing and all date cells empty
            if (($intCSID === 0) && $this->rowIsEmpty($rowDates)) {
                continue;
            }
            yield [
                'line' => $r,
                'data' => [
                    'intCSID' => $intCSID,
                    'dates'   => $rowDates,
                ],
            ];
        }
    }

    /**
     * Upsert across multiple dates from the "attendance_all" sheet.
     *
     * Required columns per row:
     *  - attendance_date (YYYY-MM-DD)
     *  - period (midterm|finals)
     *  - intCSID
     * Optional:
     *  - is_present (accepted tokens)
     *  - remarks
     *
     * @param iterable $rows
     * @param int $classlistId
     * @param int|null $actorId
     * @return array
     */
    public function upsertAll(iterable $rows, int $classlistId, ?int $actorId = null): array
    {
        $updated = 0; $skipped = 0;
        $createdDates = 0; $seededRows = 0;
        $errors = [];

        // Build roster CSIDs map for the classlist
        $roster = DB::table('tb_mas_classlist_student')
            ->where('intClassListID', $classlistId)
            ->select('intCSID')
            ->pluck('intCSID')
            ->all();
        $rosterMap = array_fill_keys(array_map('intval', $roster), true);

        // Preload existing dates for classlist
        $dates = DB::table('tb_mas_classlist_attendance_date')
            ->where('intClassListID', $classlistId)
            ->select('intID', 'attendance_date', 'period')
            ->get();

        $dateMap = []; // key: "YYYY-MM-DD|period" => intID
        foreach ($dates as $d) {
            $key = (string) ($d->attendance_date) . '|' . strtolower((string) $d->period);
            $dateMap[$key] = (int) $d->intID;
        }

        $now = Date::now();

        foreach ($rows as $item) {
            $line = (int) ($item['line'] ?? 0);
            $data = (array) ($item['data'] ?? []);

            $attendanceDate = $this->get($data, ['attendance_date', 'date'], null);
            $period = strtolower((string) $this->get($data, ['period'], ''));
            $intCSID = $this->get($data, ['intcsid', 'intCSID', 'csid'], null);
            $isPres = $this->get($data, ['is_present', 'ispresent'], null);
            $remarks = $this->get($data, ['remarks'], null);

            // Basic validations
            if (!$attendanceDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $attendanceDate)) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_DATE', 'message' => 'attendance_date must be YYYY-MM-DD.'];
                continue;
            }
            if (!in_array($period, ['midterm', 'finals'], true)) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_PERIOD', 'message' => 'period must be midterm or finals.'];
                continue;
            }
            if ($intCSID === null || $intCSID === '') {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'MISSING_INTCSID', 'message' => 'Missing intCSID column.'];
                continue;
            }
            $intCSID = (int) $intCSID;
            if ($intCSID <= 0) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_INTCSID', 'message' => 'Invalid intCSID value.'];
                continue;
            }
            if (!isset($rosterMap[$intCSID])) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'CSID_NOT_IN_CLASSLIST', 'message' => 'intCSID does not belong to this classlist.'];
                continue;
            }

            // Resolve or create/seed attendance date
            $key = $attendanceDate . '|' . $period;
            if (!isset($dateMap[$key])) {
                // Use service to create/seed idempotently
                /** @var ClasslistAttendanceService $svc */
                $svc = app(ClasslistAttendanceService::class);
                try {
                    $out = $svc->createDate($classlistId, $attendanceDate, $period, $actorId);
                    $dateMap[$key] = (int) ($out['id'] ?? 0);
                    if (!empty($out['seeded'])) {
                        $seededRows += (int) $out['seeded'];
                    }
                    $createdDates++;
                } catch (\Throwable $e) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'CREATE_DATE_FAILED', 'message' => $e->getMessage()];
                    continue;
                }
            }
            $dateId = (int) $dateMap[$key];
            if ($dateId <= 0) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_DATE_ID', 'message' => 'Failed to resolve attendance date.'];
                continue;
            }

            // Normalize is_present
            [$norm, $ok] = $this->normalizeIsPresent($isPres);
            if (!$ok) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_IS_PRESENT', 'message' => 'Invalid is_present value.'];
                continue;
            }

            // Remarks rules
            if ($norm === true || $norm === null) {
                $remarks = null;
            } else {
                if ($remarks !== null) {
                    $remarks = trim((string) $remarks);
                    if ($remarks === '') {
                        $remarks = null;
                    } elseif (mb_strlen($remarks) > 255) {
                        $remarks = mb_substr($remarks, 0, 255);
                    }
                }
            }

            // Ensure row exists (seeding should have created it; but guard in case)
            $exists = DB::table('tb_mas_classlist_attendance')
                ->where('intAttendanceDateID', $dateId)
                ->where('intCSID', $intCSID)
                ->exists();

            if (!$exists) {
                try {
                    DB::table('tb_mas_classlist_attendance')->insert([
                        'intAttendanceDateID' => $dateId,
                        'intClassListID'      => $classlistId,
                        'intCSID'             => $intCSID,
                        'intStudentID'        => 0,
                        'is_present'          => null,
                        'remarks'             => null,
                        'marked_by'           => null,
                        'marked_at'           => null,
                    ]);
                    // treat as seeded
                    $seededRows += 1;
                } catch (\Throwable $e) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'ROW_CREATE_FAILED', 'message' => $e->getMessage()];
                    continue;
                }
            }

            // Update row
            $aff = DB::table('tb_mas_classlist_attendance')
                ->where('intAttendanceDateID', $dateId)
                ->where('intCSID', $intCSID)
                ->update([
                    'is_present' => $norm,
                    'remarks'    => $remarks,
                    'marked_by'  => $actorId,
                    'marked_at'  => $now,
                ]);

            if ($aff > 0) {
                $updated += $aff;
            } else {
                $skipped++;
            }
        }

        $total = $updated + $skipped;

        return [
            'updated'        => $updated,
            'skipped'        => $skipped,
            'created_dates'  => $createdDates,
            'seeded_rows'    => $seededRows,
            'totalRows'      => $total,
            'errors'         => $errors,
        ];
    }

    /**
     * Upsert across multiple dates from the "attendance_matrix" sheet.
     * For each row (intCSID) and each date column:
     *  - Ensure attendance date (classlist + date + period) exists (idempotent create via service)
     *  - Update is_present with normalized (1,0,blank) and clear remarks
     *
     * @param iterable $rows generator from parseXlsxMatrix()
     * @param int $classlistId
     * @param string $period 'midterm'|'finals'
     * @param int|null $actorId
     * @return array
     */
    public function upsertMatrix(iterable $rows, int $classlistId, string $period, ?int $actorId = null): array
    {
        $period = strtolower(trim((string) $period));
        if (!in_array($period, ['midterm', 'finals'], true)) {
            throw new RuntimeException('Invalid period. Accepted values are midterm or finals.');
        }

        $updated = 0; $skipped = 0;
        $createdDates = 0; $seededRows = 0;
        $errors = [];

        // Build roster CSIDs map for the classlist
        $roster = DB::table('tb_mas_classlist_student')
            ->where('intClassListID', $classlistId)
            ->select('intCSID')
            ->pluck('intCSID')
            ->all();
        $rosterMap = array_fill_keys(array_map('intval', $roster), true);

        // Preload existing dates for classlist + period
        $dates = DB::table('tb_mas_classlist_attendance_date')
            ->where('intClassListID', $classlistId)
            ->where('period', $period)
            ->select('intID', 'attendance_date', 'period')
            ->get();

        $dateMap = []; // key: "YYYY-MM-DD|period" => intID
        foreach ($dates as $d) {
            $key = (string) ($d->attendance_date) . '|' . strtolower((string) $d->period);
            $dateMap[$key] = (int) $d->intID;
        }

        $now = Date::now();

        foreach ($rows as $item) {
            $line = (int) ($item['line'] ?? 0);
            $data = (array) ($item['data'] ?? []);

            $intCSID = isset($data['intCSID']) ? (int) $data['intCSID'] : 0;
            $dateValues = (array) ($data['dates'] ?? []);

            if ($intCSID <= 0) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'INVALID_INTCSID', 'message' => 'Invalid or missing intCSID value.'];
                continue;
            }
            if (!isset($rosterMap[$intCSID])) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'CSID_NOT_IN_CLASSLIST', 'message' => 'intCSID does not belong to this classlist.'];
                continue;
            }

            // Iterate each date column in this row
            foreach ($dateValues as $attendanceDate => $val) {
                // Validate date header
                if (!$attendanceDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $attendanceDate)) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'INVALID_DATE', 'message' => 'Invalid date header: ' . (string) $attendanceDate];
                    continue;
                }

                // Resolve or create date row for (classlistId, attendanceDate, period)
                $key = $attendanceDate . '|' . $period;
                if (!isset($dateMap[$key])) {
                    /** @var ClasslistAttendanceService $svc */
                    $svc = app(ClasslistAttendanceService::class);
                    try {
                        $out = $svc->createDate($classlistId, $attendanceDate, $period, $actorId);
                        $dateMap[$key] = (int) ($out['id'] ?? 0);
                        if (!empty($out['seeded'])) {
                            $seededRows += (int) $out['seeded'];
                        }
                        $createdDates++;
                    } catch (\Throwable $e) {
                        $skipped++;
                        $errors[] = ['line' => $line, 'code' => 'CREATE_DATE_FAILED', 'message' => $e->getMessage()];
                        continue;
                    }
                }
                $dateId = (int) $dateMap[$key];
                if ($dateId <= 0) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'INVALID_DATE_ID', 'message' => 'Failed to resolve attendance date.'];
                    continue;
                }

                // Normalize is_present (accept 1,0,blank but tolerate other tokens via existing normalizer)
                [$norm, $ok] = $this->normalizeIsPresent($val);
                if (!$ok) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'INVALID_IS_PRESENT', 'message' => 'Invalid is_present value at date ' . $attendanceDate];
                    continue;
                }

                // Ensure row exists (guard; seeding should have created it)
                $exists = DB::table('tb_mas_classlist_attendance')
                    ->where('intAttendanceDateID', $dateId)
                    ->where('intCSID', $intCSID)
                    ->exists();

                if (!$exists) {
                    try {
                        DB::table('tb_mas_classlist_attendance')->insert([
                            'intAttendanceDateID' => $dateId,
                            'intClassListID'      => $classlistId,
                            'intCSID'             => $intCSID,
                            'intStudentID'        => 0,
                            'is_present'          => null,
                            'remarks'             => null,
                            'marked_by'           => null,
                            'marked_at'           => null,
                        ]);
                        $seededRows += 1;
                    } catch (\Throwable $e) {
                        $skipped++;
                        $errors[] = ['line' => $line, 'code' => 'ROW_CREATE_FAILED', 'message' => $e->getMessage()];
                        continue;
                    }
                }

                // Update row; remarks are not used in matrix mode
                $aff = DB::table('tb_mas_classlist_attendance')
                    ->where('intAttendanceDateID', $dateId)
                    ->where('intCSID', $intCSID)
                    ->update([
                        'is_present' => $norm,
                        'remarks'    => null,
                        'marked_by'  => $actorId,
                        'marked_at'  => $now,
                    ]);

                if ($aff > 0) {
                    $updated += $aff;
                } else {
                    $skipped++;
                }
            }
        }

        $total = $updated + $skipped;

        return [
            'updated'        => $updated,
            'skipped'        => $skipped,
            'created_dates'  => $createdDates,
            'seeded_rows'    => $seededRows,
            'totalRows'      => $total,
            'errors'         => $errors,
        ];
    }
}
