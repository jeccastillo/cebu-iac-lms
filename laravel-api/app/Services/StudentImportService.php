<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

/**
 * StudentImportService
 *
 * Responsibilities:
 * - Introspect tb_mas_users columns and build template headers with substitutions:
 *     intProgramID -> Program Code
 *     intCurriculumID -> Curriculum Code
 *     campus_id -> Campus
 * - Generate an XLSX template
 * - Parse .xlsx/.xls/.csv files into normalized rows
 * - Resolve Program/Curriculum/Campus codes to IDs
 * - Upsert tb_mas_users rows by strStudentNumber (update if exists, else insert)
 */
class StudentImportService
{
    // Columns that must never be written by import for safety
    private array $prohibitedWriteColumns = [
        'intID', 'slug', 'strPass', 'strReset', 'dteCreated', 'created_at', 'updated_at',
    ];

    // Header substitutions mapping template header -> write column name OR special key
    private const HEADER_SUBS = [
        'intProgramID'    => 'Program Code',
        'intCurriculumID' => 'Curriculum Code',
        'campus_id'       => 'Campus',
    ];

    // Reverse mapping for normalization (template input -> tb_mas_users column)
    private const REVERSE_HEADER = [
        'program code'    => 'intProgramID',
        'curriculum code' => 'intCurriculumID',
        'campus'          => 'campus_id',
    ];

    public function buildTemplateColumns(): array
    {
        $cols = [];
        try {
            if (Schema::hasTable('tb_mas_users')) {
                $cols = Schema::getColumnListing('tb_mas_users');
            }
        } catch (\Throwable $e) {
            // Fallback to a reasonable subset if schema introspection fails
            $cols = [
                'strStudentNumber', 'strUsername', 'strEmail', 'strGSuiteEmail',
                'strFirstname', 'strMiddlename', 'strLastname', 'enumGender',
                'dteBirthDate', 'intProgramID', 'intCurriculumID', 'campus_id',
                'student_status', 'student_type', 'level', 'strMobileNumber',
            ];
        }

        // Build headers with substitutions and exclude prohibited
        $headers = [];
        foreach ($cols as $c) {
            if (in_array($c, $this->prohibitedWriteColumns, true)) {
                continue;
            }
            if (array_key_exists($c, self::HEADER_SUBS)) {
                $headers[] = self::HEADER_SUBS[$c];
            } else {
                $headers[] = $c;
            }
        }

        // Ensure strStudentNumber exists and is prominent
        if (!in_array('strStudentNumber', $headers, true)) {
            array_unshift($headers, 'strStudentNumber');
        } else {
            // Move to front
            $headers = array_values(array_unique(array_merge(['strStudentNumber'], $headers)));
        }

        return $headers;
    }

    public function generateTemplateXlsx(): Spreadsheet
    {
        $headers = $this->buildTemplateColumns();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('students');

        // Header row
        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx, 1, $h);
            // Bold header + auto width
            $sheet->getStyleByColumnAndRow($colIdx, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
            $colIdx++;
        }

        // Optional: add a notes sheet
        try {
            $notes = $spreadsheet->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $notes->setCellValue('A2', 'Fill the "Program Code", "Curriculum Code", and "Campus" with valid values.');
            $notes->setCellValue('A3', 'Campus must match tb_mas_campuses.campus_name (case-insensitive exact).');
            $notes->setCellValue('A4', 'Rows are updated when strStudentNumber already exists; otherwise inserted.');
        } catch (\Throwable $e) {
            // ignore if sheet creation fails
        }

        return $spreadsheet;
    }

    /**
     * Parse uploaded file into generator of normalized rows (associative arrays).
     *
     * @param string $path
     * @param string $ext one of xlsx|xls|csv
     * @return \Generator<array> yielding ['line' => int, 'data' => array]
     */
    public function parse(string $path, string $ext): \Generator
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader(ucfirst($ext) === 'XLS' ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();

            // Read header row (row 1)
            $headerMap = [];
            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            for ($c = 1; $c <= $highestCol; $c++) {
                $v = (string) $sheet->getCellByColumnAndRow($c, 1)->getValue();
                $v = trim($v);
                if ($v !== '') {
                    $headerMap[$c] = strtolower($v);
                }
            }

            $rowNum = 2;
            $highestRow = (int) $sheet->getHighestRow();
            for (; $rowNum <= $highestRow; $rowNum++) {
                $row = [];
                for ($c = 1; $c <= $highestCol; $c++) {
                    if (!isset($headerMap[$c])) continue;
                    $key = $headerMap[$c];
                    $val = $sheet->getCellByColumnAndRow($c, $rowNum)->getFormattedValue();
                    $row[$key] = is_string($val) ? trim($val) : $val;
                }
                // Skip completely empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                yield ['line' => $rowNum, 'data' => $row];
            }
        } elseif ($ext === 'csv') {
            $fh = fopen($path, 'rb');
            if ($fh === false) {
                throw new RuntimeException('Unable to open uploaded CSV.');
            }
            $header = null;
            $line = 0;
            while (($cols = fgetcsv($fh)) !== false) {
                $line++;
                // Normalize encoding
                $cols = array_map(function ($v) {
                    if ($v === null) return null;
                    $s = (string) $v;
                    return trim($s);
                }, $cols);

                if ($header === null) {
                    $header = array_map(fn($h) => strtolower((string) $h), $cols);
                    continue;
                }

                $row = [];
                foreach ($header as $i => $h) {
                    if ($h === '' || !array_key_exists($i, $cols)) continue;
                    $row[$h] = $cols[$i];
                }
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                // CSV first data line corresponds to Excel row 2: add +1
                yield ['line' => $line, 'data' => $row];
            }
            fclose($fh);
        } else {
            throw new RuntimeException('Unsupported file extension: ' . $ext);
        }
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Normalize a parsed row into writeable tb_mas_users columns.
     * - Applies header reverse mapping
     * - Filters prohibited columns
     * - Returns tuple [array $userCols, array $meta] where $meta contains
     *   'student_number', 'program_code', 'curriculum_code', 'campus_name'.
     */
    public function normalizeRow(array $row): array
    {
        $userCols = [];
        $meta = [
            'student_number'  => null,
            'program_code'    => null,
            'curriculum_code' => null,
            'campus_name'     => null,
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = is_string($v) ? trim($v) : $v;

            // Handle meta fields from substituted headers
            if ($lk === 'program code') {
                $meta['program_code'] = (string) $val;
                // write column after FK resolution: intProgramID
                continue;
            }
            if ($lk === 'curriculum code') {
                $meta['curriculum_code'] = (string) $val;
                continue;
            }
            if ($lk === 'campus') {
                $meta['campus_name'] = (string) $val;
                continue;
            }

            // Map to actual write column for non-meta headers
            $targetCol = self::REVERSE_HEADER[$lk] ?? $k;

            // Deny prohibited columns
            if (in_array($targetCol, $this->prohibitedWriteColumns, true)) {
                continue;
            }

            // Core key used for insert/update switch
            if ($targetCol === 'strStudentNumber') {
                $meta['student_number'] = (string) $val;
            }

            // Normalize blanks to null
            if ($val === '') {
                $val = null;
            }

            $userCols[$targetCol] = $val;
        }

        return [$userCols, $meta];
    }

    /**
     * Resolve and apply foreign keys on $userCols given $meta.
     * - Program Code -> tb_mas_programs.intProgramID by strProgramCode
     * - Curriculum Code -> tb_mas_curriculum.intID by strName
     * - Campus -> tb_mas_campuses.id by campus_name (case-insensitive exact)
     *
     * Throws RuntimeException on any unresolved mapping.
     */
    public function resolveForeigns(array &$userCols, array $meta): void
    {
        if (!empty($meta['program_code'])) {
            $pid = DB::table('tb_mas_programs')
                ->whereRaw('LOWER(strProgramCode) = ?', [strtolower($meta['program_code'])])
                ->value('intProgramID');
            if ($pid === null) {
                throw new RuntimeException('Program Code not found: ' . $meta['program_code']);
            }
            $userCols['intProgramID'] = (int) $pid;
        }

        if (!empty($meta['curriculum_code'])) {
            $cid = DB::table('tb_mas_curriculum')
                ->whereRaw('LOWER(strName) = ?', [strtolower($meta['curriculum_code'])])
                ->value('intID');
            if ($cid === null) {
                throw new RuntimeException('Curriculum Code not found: ' . $meta['curriculum_code']);
            }
            $userCols['intCurriculumID'] = (int) $cid;
        }

        if (!empty($meta['campus_name'])) {
            $campusId = DB::table('tb_mas_campuses')
                ->whereRaw('LOWER(campus_name) = ?', [strtolower($meta['campus_name'])])
                ->value('id');
            if ($campusId === null) {
                throw new RuntimeException('Campus not found: ' . $meta['campus_name']);
            }
            $userCols['campus_id'] = (int) $campusId;
        }
    }

    /**
     * Upsert rows in chunks by strStudentNumber:
     * - Update: only provided columns are updated
     * - Insert: provided columns; omitted columns default to NULL/DB defaults
     *
     * @param iterable $rowIter yields ['line' => int, 'data' => array]
     * @param bool $dryRun if true, validate only without DB writes
     * @return array result summary: [totalRows, inserted, updated, skipped, errors[]]
     */
    public function upsertRows(iterable $rowIter, bool $dryRun = false): array
    {
        $total = 0; $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        $chunk = [];
        $CHUNK_SIZE = 200;

        $flush = function () use (&$chunk, &$ins, &$upd, &$skp, &$errors, $dryRun) {
            if (empty($chunk)) return;

            DB::beginTransaction();
            try {
                foreach ($chunk as $item) {
                    $line = $item['line'];
                    [$userCols, $meta] = $this->normalizeRow($item['data']);

                    $sn = $meta['student_number'];
                    if ($sn === null || trim($sn) === '') {
                        $skp++;
                        $errors[] = ['line' => $line, 'student_number' => null, 'message' => 'Missing strStudentNumber'];
                        continue;
                    }

                    try {
                        $this->resolveForeigns($userCols, $meta);
                    } catch (\Throwable $e) {
                        $skp++;
                        $errors[] = ['line' => $line, 'student_number' => $sn, 'message' => $e->getMessage()];
                        continue;
                    }

                    if ($dryRun) {
                        // Count as would-update or would-insert
                        $exists = DB::table('tb_mas_users')->where('strStudentNumber', $sn)->exists();
                        if ($exists) $upd++; else $ins++;
                        continue;
                    }

                    $existing = DB::table('tb_mas_users')->where('strStudentNumber', $sn)->first();

                    if ($existing) {
                        // Partial update: only columns present in userCols
                        if (!empty($userCols)) {
                            DB::table('tb_mas_users')
                                ->where('strStudentNumber', $sn)
                                ->update($userCols);
                        }
                        $upd++;
                    } else {
                        // Insert: ensure strStudentNumber is included
                        $payload = array_merge(['strStudentNumber' => $sn], $userCols);
                        DB::table('tb_mas_users')->insert($payload);
                        $ins++;
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                // Mark entire chunk failed (conservative)
                foreach ($chunk as $item) {
                    $line = $item['line'];
                    $row = $item['data'];
                    $sn = isset($row['strstudentnumber']) ? (string) $row['strstudentnumber'] : (isset($row['strStudentNumber']) ? (string) $row['strStudentNumber'] : null);
                    $errors[] = ['line' => $line, 'student_number' => $sn, 'message' => 'DB error: ' . $e->getMessage()];
                    $skp++;
                }
            }

            // Clear chunk
            $chunk = [];
        };

        foreach ($rowIter as $item) {
            $total++;
            $chunk[] = $item;
            if (count($chunk) >= $CHUNK_SIZE) {
                $flush();
            }
        }
        // Flush remainder
        $flush();

        return [
            'totalRows' => $total,
            'inserted'  => $ins,
            'updated'   => $upd,
            'skipped'   => $skp,
            'errors'    => $errors,
        ];
    }
}
