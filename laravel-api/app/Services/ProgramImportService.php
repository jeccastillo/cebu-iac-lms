<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

class ProgramImportService
{
    // Sheet and headers
    private const SHEET_PROGRAMS = 'programs';

    // Template headers (display names)
    private const PROGRAM_HEADERS = [
        'Program ID',             // optional explicit PK (tb_mas_programs.intProgramID)
        'Program Code',           // tb_mas_programs.strProgramCode
        'Program Description',    // tb_mas_programs.strProgramDescription
        'Major',                  // tb_mas_programs.strMajor
        'Type',                   // tb_mas_programs.type
        'School',                 // tb_mas_programs.school
        'Short Name',             // tb_mas_programs.short_name
        'Default Curriculum ID',  // tb_mas_programs.default_curriculum (FK to tb_mas_curriculum.intID)
        'Enabled',                // tb_mas_programs.enumEnabled (0|1; default 1)
        'Campus',                 // resolves to tb_mas_programs.campus_id via tb_mas_campuses.campus_name (case-insensitive)
    ];

    /**
     * Build the template spreadsheet.
     */
    public function generateTemplateXlsx(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(self::SHEET_PROGRAMS);

        $col = 1;
        foreach (self::PROGRAM_HEADERS as $h) {
            $sheet->setCellValueByColumnAndRow($col, 1, $h);
            $sheet->getStyleByColumnAndRow($col, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            $col++;
        }

        // Notes
        try {
            $notes = $spreadsheet->createSheet();
            $notes->setTitle('Notes');
            $r = 1;
            $notes->setCellValue('A'.$r, 'Instructions'); $notes->getStyle('A'.$r)->getFont()->setBold(true); $r++;
            $notes->setCellValue('A'.$r++, 'Sheet: "programs"');
            $notes->setCellValue('A'.$r++, '  - Program ID (optional): when provided, updates existing row with that ID; if not found, insert is attempted using that ID (DB rules apply).');
            $notes->setCellValue('A'.$r++, '  - Program Code/Description/Major/Type/School/Short Name: optional strings; trimmed; blanks treated as NULL.');
            $notes->setCellValue('A'.$r++, '  - Default Curriculum ID: optional integer; validated against tb_mas_curriculum.intID (no name-based resolution).');
            $notes->setCellValue('A'.$r++, '  - Enabled: 0|1; blank defaults to 1.');
            $notes->setCellValue('A'.$r++, '  - Campus: when provided, must match tb_mas_campuses.campus_name (case-insensitive); Admin page may apply a campus override to all rows.');
            $r++;
            $notes->setCellValue('A'.$r, 'Behavior'); $notes->getStyle('A'.$r)->getFont()->setBold(true); $r++;
            $notes->setCellValue('A'.$r++, '  - Upsert identity uses Program ID when provided; otherwise insert as new row.');
            $notes->setCellValue('A'.$r++, '  - Dry run validates without writing and returns would-insert/update counts plus row-level errors.');
        } catch (\Throwable $e) {
            // ignore
        }

        return $spreadsheet;
    }

    /**
     * Parse uploaded file into generator of normalized rows (associative arrays with lower-cased headers).
     *
     * @param string $path
     * @param string $ext one of xlsx|xls|csv
     * @return \Generator<array{line:int,data:array}>
     */
    public function parse(string $path, string $ext): \Generator
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader($ext === 'xls' ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);

            $sheet = $spreadsheet->getSheetByName(self::SHEET_PROGRAMS) ?: $spreadsheet->getSheet(0);

            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            $highestRow = (int) $sheet->getHighestRow();

            // Header map
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
                if ($this->isEmptyRow($row)) continue;
                yield ['line' => $r, 'data' => $row];
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
                $cols = array_map(function ($v) {
                    $s = (string) ($v ?? '');
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
                if ($this->isEmptyRow($row)) continue;
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
            if ($v !== null && $v !== '') return false;
        }
        return true;
    }

    private function coerceNullish($val) {
        if ($val === null) return null;
        if (is_string($val)) {
            $s = trim($val);
            if ($s === '') return null;
            $lower = strtolower($s);
            if (in_array($lower, ['null','n/a','na','none','nil'], true)) return null;
            return $s;
        }
        return $val;
    }

    /**
     * Normalize a parsed row into writeable tb_mas_programs columns.
     * Returns [array $cols, array $meta]
     *  - $cols: columns for tb_mas_programs (without intProgramID)
     *  - $meta: ['program_id' => ?int, 'campus_name' => ?string]
     */
    public function normalizeRow(array $row): array
    {
        $cols = [
            'strProgramCode'        => null,
            'strProgramDescription' => null,
            'strMajor'              => '',
            'type'                  => null,
            'school'                => null,
            'short_name'            => '',
            'default_curriculum'    => null,
            'enumEnabled'           => 1,
            // 'campus_id' set by resolveForeigns()
        ];
        $meta = [
            'program_id'  => null,
            'campus_name' => null,
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = $this->coerceNullish($v);

            if ($lk === 'program id' || $lk === 'intprogramid') {
                if ($val !== null && $val !== '') {
                    $meta['program_id'] = (int) $val;
                }
            } elseif ($lk === 'program code' || $lk === 'strprogramcode') {
                $cols['strProgramCode'] = $val === null ? null : (string) $val;
            } elseif ($lk === 'program description' || $lk === 'strprogramdescription') {
                $cols['strProgramDescription'] = $val === null ? null : (string) $val;
            } elseif ($lk === 'major' || $lk === 'strmajor') {
                $cols['strMajor'] = $val === null ? '' : (string) $val;
            } elseif ($lk === 'type') {
                $cols['type'] = $val === null ? null : (string) $val;
            } elseif ($lk === 'school') {
                $cols['school'] = $val === null ? null : (string) $val;
            } elseif ($lk === 'short name' || $lk === 'short_name') {
                $cols['short_name'] = $val === null ? '' : (string) $val;
            } elseif ($lk === 'default curriculum id' || $lk === 'default_curriculum') {
                if ($val === null || $val === '') {
                    $cols['default_curriculum'] = null;
                } else {
                    // keep as int-like; existence validated later
                    $cols['default_curriculum'] = (int) $val;
                }
            } elseif ($lk === 'enabled' || $lk === 'enumenabled') {
                if ($val === null || $val === '') {
                    $cols['enumEnabled'] = 1;
                } else {
                    $cols['enumEnabled'] = (int) $val ? 1 : 0;
                }
            } elseif ($lk === 'campus') {
                $meta['campus_name'] = $val === null ? null : (string) $val;
            }
        }

        return [$cols, $meta];
    }

    /**
     * Apply Campus resolution and override.
     */
    public function resolveForeigns(array &$cols, array $meta, ?int $forcedCampusId = null): void
    {
        if ($forcedCampusId !== null) {
            $cols['campus_id'] = (int) $forcedCampusId;
            return;
        }

        if (!empty($meta['campus_name'])) {
            $campusId = DB::table('tb_mas_campuses')
                ->whereRaw('LOWER(campus_name) = ?', [strtolower($meta['campus_name'])])
                ->value('id');
            if ($campusId === null) {
                throw new RuntimeException('Campus not found: ' . $meta['campus_name']);
            }
            $cols['campus_id'] = (int) $campusId;
        }
        // If neither override nor Campus provided: leave campus_id as-is (nullable).
    }

    /**
     * Validate foreign keys that are present on $cols.
     * Mutates $cols by reference to nullify invalid FKs.
     */
    public function validateForeignKeyExistence(array &$cols): void
    {
        if (isset($cols['default_curriculum']) && $cols['default_curriculum'] !== null) {
            $exists = DB::table('tb_mas_curriculum')
                ->where('intID', (int) $cols['default_curriculum'])
                ->exists();
            if (!$exists) {
                // Requirement: When uploading programs CSV, if default curriculum id is not found, set it to null.
                $cols['default_curriculum'] = null;
            }
        }
    }

    /**
     * Perform upserts.
     *
     * @param iterable $rowIter yields ['line'=>int,'data'=>array]
     * @param bool $dryRun validate only when true
     * @param ?int $forcedCampusId campus override, applied to all rows when provided
     * @return array{totalRows:int,inserted:int,updated:int,skipped:int,errors:array<int,array>}
     */
    public function upsertRows(iterable $rowIter, bool $dryRun = false, ?int $forcedCampusId = null): array
    {
        $total = 0; $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        foreach ($rowIter as $item) {
            $total++;
            $line = $item['line'] ?? 0;
            $data = $item['data'] ?? [];

            try {
                [$cols, $meta] = $this->normalizeRow($data);
            } catch (\Throwable $e) {
                $skp++;
                $errors[] = ['line' => $line, 'program_id' => null, 'message' => 'Normalize error: ' . $e->getMessage()];
                continue;
            }

            // Basic sanity check: at least one meaningful value
            $hasMeaningful = false;
            foreach (['strProgramCode','strProgramDescription','strMajor','type','school','short_name','default_curriculum','enumEnabled'] as $ck) {
                if (array_key_exists($ck, $cols) && $cols[$ck] !== null && $cols[$ck] !== '') {
                    $hasMeaningful = true; break;
                }
            }
            if (!$hasMeaningful && $forcedCampusId === null) {
                // if nothing to write and no campus override, skip
                $skp++;
                $errors[] = ['line' => $line, 'program_id' => $meta['program_id'], 'message' => 'Empty row'];
                continue;
            }

            try {
                $this->resolveForeigns($cols, $meta, $forcedCampusId);
            } catch (\Throwable $e) {
                $skp++;
                $errors[] = ['line' => $line, 'program_id' => $meta['program_id'], 'message' => $e->getMessage()];
                continue;
            }

            try {
                $this->validateForeignKeyExistence($cols);
            } catch (\Throwable $e) {
                $skp++;
                $errors[] = ['line' => $line, 'program_id' => $meta['program_id'], 'message' => $e->getMessage()];
                continue;
            }

            $pid = $meta['program_id'];

            if ($dryRun) {
                if ($pid !== null) {
                    $exists = DB::table('tb_mas_programs')->where('intProgramID', (int) $pid)->exists();
                    if ($exists) $upd++; else $ins++;
                } else {
                    $ins++;
                }
                continue;
            }

            try {
                DB::beginTransaction();

                if ($pid !== null) {
                    $existing = DB::table('tb_mas_programs')->where('intProgramID', (int) $pid)->first();
                    if ($existing) {
                        // partial update
                        if (!empty($cols)) {
                            DB::table('tb_mas_programs')->where('intProgramID', (int) $pid)->update($cols);
                        }
                        $upd++;
                    } else {
                        // attempt explicit PK insert
                        $payload = array_merge(['intProgramID' => (int) $pid], $cols);
                        DB::table('tb_mas_programs')->insert($payload);
                        $ins++;
                    }
                } else {
                    // insert with auto-increment PK
                    DB::table('tb_mas_programs')->insert($cols);
                    $ins++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $skp++;
                $errors[] = ['line' => $line, 'program_id' => $pid, 'message' => 'DB error: ' . $e->getMessage()];
                continue;
            }
        }

        return [
            'totalRows' => $total,
            'inserted'  => $ins,
            'updated'   => $upd,
            'skipped'   => $skp,
            'errors'    => $errors,
        ];
    }
}
