<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class CurriculumImportService
{
    // Headers for template generation
    private const SHEET_CURRICULA = 'curricula';
    private const SHEET_SUBJECTS = 'curriculum_subjects';

    private const CURRICULA_HEADERS = [
        'Name',          // tb_mas_curriculum.strName
        'Program Code',  // resolves to tb_mas_programs.intProgramID via strProgramCode
        'Campus',        // resolves to tb_mas_campuses.id via campus_name
        'Active',        // 0|1 -> tb_mas_curriculum.active
        'Enhanced',      // 0|1 -> tb_mas_curriculum.isEnhanced
    ];

    private const SUBJECTS_HEADERS = [
        'Curriculum Name', // join back to curriculum by (Name+Program Code+Campus)
        'Program Code',
        'Campus',
        'Subject Code',    // resolves to tb_mas_subjects.intID via strCode
        'Year Level',      // 1..10 -> tb_mas_curriculum_subject.intYearLevel
        'Sem',             // 1..3  -> tb_mas_curriculum_subject.intSem
    ];

    public function generateTemplateXlsx(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: curricula
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle(self::SHEET_CURRICULA);
        $this->writeHeaderRow($sheet1, self::CURRICULA_HEADERS);

        // Sheet 2: curriculum_subjects
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle(self::SHEET_SUBJECTS);
        $this->writeHeaderRow($sheet2, self::SUBJECTS_HEADERS);

        // Notes sheet
        try {
            $notes = $spreadsheet->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $row = 2;
            $notes->setCellValue('A' . $row++, 'Sheet: "curricula"');
            $notes->setCellValue('A' . $row++, '  - Name: curriculum name (required).');
            $notes->setCellValue('A' . $row++, '  - Program Code: must match tb_mas_programs.strProgramCode (case-insensitive).');
            $notes->setCellValue('A' . $row++, '  - Campus: must match tb_mas_campuses.campus_name (case-insensitive).');
            $notes->setCellValue('A' . $row++, '  - Active (0|1), Enhanced (0|1) optional; defaults 1 and 0 respectively.');

            $row++;
            $notes->setCellValue('A' . $row++, 'Sheet: "curriculum_subjects"');
            $notes->setCellValue('A' . $row++, '  - Curriculum Name + Program Code + Campus: identify which curriculum to link the subject to.');
            $notes->setCellValue('A' . $row++, '  - Subject Code: must match tb_mas_subjects.strCode (case-insensitive).');
            $notes->setCellValue('A' . $row++, '  - Year Level: integer 1..10; Sem: integer 1..3.');

            $row++;
            $notes->setCellValue('A' . $row++, 'Behavior');
            $notes->setCellValue('A' . $row++, '  - Curricula are upserted by (Name + Program Code + Campus).');
            $notes->setCellValue('A' . $row++, '  - Subject links are upserted by (Curriculum ID + Subject ID); Year/Sem are updated when link exists.');
        } catch (\Throwable $e) {
            // ignore
        }

        return $spreadsheet;
    }

    private function writeHeaderRow($sheet, array $headers): void
    {
        $c = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($c, 1, $h);
            $sheet->getStyleByColumnAndRow($c, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            $c++;
        }
    }

    /**
     * Parse uploaded file. Returns an array with two iterables:
     * [
     *   'curricula' => iterable of ['line'=>int, 'data'=>array],
     *   'subjects'  => iterable of ['line'=>int, 'data'=>array],
     * ]
     * For CSV, only 'curricula' is parsed (single-sheet).
     */
    public function parse(string $path, string $ext): array
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader(($ext === 'xls') ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);

            $curriculaSheet = $spreadsheet->getSheetByName(self::SHEET_CURRICULA) ?: $spreadsheet->getSheet(0);
            $subjectsSheet = $spreadsheet->getSheetByName(self::SHEET_SUBJECTS);

            $curriculaRows = $this->parseSheet($curriculaSheet);
            $subjectsRows = $subjectsSheet ? $this->parseSheet($subjectsSheet) : $this->emptyGenerator();

            return [
                'curricula' => $curriculaRows,
                'subjects'  => $subjectsRows,
            ];
        } elseif ($ext === 'csv') {
            // CSV: treat as curricula-only
            return [
                'curricula' => $this->parseCsv($path),
                'subjects'  => $this->emptyGenerator(),
            ];
        } else {
            throw new RuntimeException('Unsupported file type for curriculum import: ' . $ext);
        }
    }

    private function emptyGenerator(): \Generator
    {
        if (false) {
            yield [];
        }
        return;
    }

    private function parseSheet($sheet): \Generator
    {
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = (int) $sheet->getHighestRow();

        // Build header map
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

    private function parseCsv(string $path): \Generator
    {
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
            if ($this->rowIsEmpty($row)) continue;
            yield ['line' => $line, 'data' => $row];
        }
        fclose($fh);
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') return false;
        }
        return true;
    }

    /**
     * Normalize one curricula row.
     * Returns [array $cols, array $meta] where:
     *  - $cols: writeable columns for tb_mas_curriculum
     *  - $meta: ['name','program_code','campus_name','key'] identity key
     */
    public function normalizeCurriculumRow(array $row): array
    {
        $meta = [
            'name' => null,
            'program_code' => null,
            'campus_name' => null,
            'key' => null,
        ];
        $cols = [
            'strName' => null,
            'active' => 1,
            'isEnhanced' => 0,
            // FKs set by resolveCurriculumFKs: intProgramID, campus_id
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = is_string($v) ? trim($v) : $v;

            // Accept multiple aliases for curriculum name
            if (in_array($lk, ['name', 'curriculum name', 'curriculum', 'curriculum_name'], true)) {
                if ($meta['name'] === null || $meta['name'] === '') {
                    $meta['name'] = (string) $val;
                    $cols['strName'] = (string) $val;
                }
            } elseif ($lk === 'program code') {
                $meta['program_code'] = (string) $val;
            } elseif ($lk === 'campus') {
                $meta['campus_name'] = (string) $val;
            } elseif ($lk === 'active') {
                $cols['active'] = (int) ((string)$val !== '' ? $val : 1);
            } elseif ($lk === 'enhanced') {
                $cols['isEnhanced'] = (int) ((string)$val !== '' ? $val : 0);
            }
        }

        $meta['key'] = $this->buildCurriculumIdentityKey($meta['name'], $meta['program_code'], $meta['campus_name']);
        return [$cols, $meta];
    }

    private function buildCurriculumIdentityKey(?string $name, ?string $programCode, ?string $campusName): string
    {
        $n = strtolower(trim((string) ($name ?? '')));
        $p = strtolower(trim((string) ($programCode ?? '')));
        $c = strtolower(trim((string) ($campusName ?? '')));
        return $n . '|' . $p . '|' . $c;
    }

    /**
     * Resolve program and campus foreign keys onto $cols.
     * Throws on resolution failure.
     */
    public function resolveCurriculumFKs(array &$cols, array $meta): void
    {
        if (empty($meta['program_code'])) {
            throw new RuntimeException('Program Code is required for curriculum "' . ($meta['name'] ?? '') . '"');
        }
        if (empty($meta['campus_name'])) {
            throw new RuntimeException('Campus is required for curriculum "' . ($meta['name'] ?? '') . '"');
        }

        $pid = DB::table('tb_mas_programs')
            ->whereRaw('LOWER(strProgramCode) = ?', [strtolower($meta['program_code'])])
            ->value('intProgramID');
        if ($pid === null) {
            throw new RuntimeException('Program Code not found: ' . $meta['program_code']);
        }

        $campusId = DB::table('tb_mas_campuses')
            ->whereRaw('LOWER(campus_name) = ?', [strtolower($meta['campus_name'])])
            ->value('id');
        if ($campusId === null) {
            throw new RuntimeException('Campus not found: ' . $meta['campus_name']);
        }

        $cols['intProgramID'] = (int) $pid;
        $cols['campus_id'] = (int) $campusId;
    }

    /**
     * Upsert curricula by identity (strName + intProgramID + campus_id).
     * Returns [inserted, updated, skipped, errors[], idMap]
     *  - idMap: key "{name}|{program_code}|{campus}" => intID
     */
    public function upsertCurricula(iterable $rows, bool $dryRun = false): array
    {
        $ins = 0; $upd = 0; $skp = 0;
        $errors = [];
        $idMap = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $line = $item['line'] ?? 0;
                $data = $item['data'] ?? [];
                [$cols, $meta] = $this->normalizeCurriculumRow($data);

                $name = $meta['name'];
                if ($name === null || trim($name) === '') {
                    $skp++;
                    $errors[] = ['sheet' => self::SHEET_CURRICULA, 'line' => $line, 'key' => null, 'message' => 'Missing Name'];
                    continue;
                }

                try {
                    $this->resolveCurriculumFKs($cols, $meta);
                } catch (\Throwable $e) {
                    $skp++;
                    $errors[] = ['sheet' => self::SHEET_CURRICULA, 'line' => $line, 'key' => $meta['key'], 'message' => $e->getMessage()];
                    continue;
                }

                // Locate existing (case-insensitive for name)
                $existing = DB::table('tb_mas_curriculum')
                    ->whereRaw('LOWER(strName) = ?', [strtolower($cols['strName'])])
                    ->where('intProgramID', $cols['intProgramID'])
                    ->where('campus_id', $cols['campus_id'])
                    ->first();

                if ($dryRun) {
                    if ($existing) $upd++; else $ins++;
                    // Build idMap using DB ID if exists (helps subjects phase)
                    if ($existing) {
                        $idMap[$meta['key']] = (int) $existing->intID;
                    }
                    continue;
                }

                if ($existing) {
                    DB::table('tb_mas_curriculum')
                        ->where('intID', $existing->intID)
                        ->update($cols);
                    $curriculumId = (int) $existing->intID;
                    $idMap[$meta['key']] = $curriculumId;
                    $upd++;
                } else {
                    $curriculumId = (int) DB::table('tb_mas_curriculum')->insertGetId($cols);
                    $idMap[$meta['key']] = $curriculumId;
                    $ins++;
                }

                // If the matched program does not have a default curriculum,
                // set the first encountered curriculum in this import as its default.
                $programId = (int) ($cols['intProgramID'] ?? 0);
                if ($programId > 0) {
                    // Cache program default values within this import run to minimize DB reads.
                    static $programDefaultCache = [];
                    if (!array_key_exists($programId, $programDefaultCache)) {
                        $currentDefault = DB::table('tb_mas_programs')
                            ->where('intProgramID', $programId)
                            ->value('default_curriculum');
                        $programDefaultCache[$programId] = (int) ($currentDefault ?? 0);
                    }
                    if (empty($programDefaultCache[$programId])) {
                        DB::table('tb_mas_programs')
                            ->where('intProgramID', $programId)
                            ->update(['default_curriculum' => $curriculumId]);
                        $programDefaultCache[$programId] = $curriculumId;
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = ['sheet' => self::SHEET_CURRICULA, 'line' => 0, 'key' => null, 'message' => 'DB error: ' . $e->getMessage()];
        }

        return [
            'inserted' => $ins,
            'updated' => $upd,
            'skipped' => $skp,
            'errors'  => $errors,
            'idMap'   => $idMap,
        ];
    }

    /**
     * Normalize one subject link row.
     * Returns [array $meta, array $link]
     *  - $meta: curriculum identity lookup fields + subject_code
     *  - $link: intYearLevel, intSem (validated ranges)
     */
    public function normalizeSubjectRow(array $row): array
    {
        $meta = [
            'curriculum_name' => null,
            'program_code' => null,
            'campus_name' => null,
            'subject_code' => null,
        ];
        $link = [
            'intYearLevel' => null,
            'intSem' => null,
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = is_string($v) ? trim($v) : $v;

            if ($lk === 'curriculum name') $meta['curriculum_name'] = (string) $val;
            elseif ($lk === 'program code') $meta['program_code'] = (string) $val;
            elseif ($lk === 'campus') $meta['campus_name'] = (string) $val;
            elseif ($lk === 'subject code') $meta['subject_code'] = (string) $val;
            elseif ($lk === 'year level') $link['intYearLevel'] = (int) ($val === '' ? 0 : $val);
            elseif ($lk === 'sem') $link['intSem'] = (int) ($val === '' ? 0 : $val);
        }

        // Validate ranges (defer resolution/ID lookups to resolveSubjectFKs)
        if (!is_int($link['intYearLevel']) || $link['intYearLevel'] < 1 || $link['intYearLevel'] > 10) {
            throw new RuntimeException('Invalid Year Level (must be 1..10)');
        }
        if (!is_int($link['intSem']) || $link['intSem'] < 1 || $link['intSem'] > 3) {
            throw new RuntimeException('Invalid Sem (must be 1..3)');
        }

        return [$meta, $link];
    }

    /**
     * Resolve curriculum and subject foreign keys onto $link.
     * Uses $curriculumIdMap if available; otherwise queries DB.
     */
    public function resolveSubjectFKs(array &$link, array $meta, array $curriculumIdMap): void
    {
        if (empty($meta['curriculum_name']) || empty($meta['program_code']) || empty($meta['campus_name'])) {
            throw new RuntimeException('Curriculum Name, Program Code, and Campus are required for subject row.');
        }
        if (empty($meta['subject_code'])) {
            throw new RuntimeException('Subject Code is required.');
        }

        $key = $this->buildCurriculumIdentityKey($meta['curriculum_name'], $meta['program_code'], $meta['campus_name']);
        $curriculumId = $curriculumIdMap[$key] ?? null;

        if (!$curriculumId) {
            // Resolve via DB (case-insensitive match for name and program/campus resolution)
            $pid = DB::table('tb_mas_programs')
                ->whereRaw('LOWER(strProgramCode) = ?', [strtolower($meta['program_code'])])
                ->value('intProgramID');
            if ($pid === null) {
                throw new RuntimeException('Program Code not found: ' . $meta['program_code']);
            }

            $campusId = DB::table('tb_mas_campuses')
                ->whereRaw('LOWER(campus_name) = ?', [strtolower($meta['campus_name'])])
                ->value('id');
            if ($campusId === null) {
                throw new RuntimeException('Campus not found: ' . $meta['campus_name']);
            }

            $curriculumId = DB::table('tb_mas_curriculum')
                ->whereRaw('LOWER(strName) = ?', [strtolower($meta['curriculum_name'])])
                ->where('intProgramID', $pid)
                ->where('campus_id', $campusId)
                ->value('intID');
            if ($curriculumId === null) {
                throw new RuntimeException('Curriculum not found for combination: ' . $meta['curriculum_name'] . ' | ' . $meta['program_code'] . ' | ' . $meta['campus_name']);
            }
        }

        $subjectId = DB::table('tb_mas_subjects')
            ->whereRaw('LOWER(strCode) = ?', [strtolower($meta['subject_code'])])
            ->value('intID');
        if ($subjectId === null) {
            throw new RuntimeException('Subject Code not found: ' . $meta['subject_code']);
        }

        $link['intCurriculumID'] = (int) $curriculumId;
        $link['intSubjectID'] = (int) $subjectId;
    }

    /**
     * Upsert subject links by (intCurriculumID + intSubjectID). Updates Year/Sem when exists.
     * Returns [inserted, updated, skipped, errors[]]
     */
    public function upsertSubjectLinks(iterable $rows, array $curriculumIdMap, bool $dryRun = false): array
    {
        $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $line = $item['line'] ?? 0;
                $data = $item['data'] ?? [];

                try {
                    [$meta, $link] = $this->normalizeSubjectRow($data);
                } catch (\Throwable $e) {
                    $skp++;
                    $errors[] = ['sheet' => self::SHEET_SUBJECTS, 'line' => $line, 'key' => null, 'message' => $e->getMessage()];
                    continue;
                }

                try {
                    $this->resolveSubjectFKs($link, $meta, $curriculumIdMap);
                } catch (\Throwable $e) {
                    $skp++;
                    $errors[] = ['sheet' => self::SHEET_SUBJECTS, 'line' => $line, 'key' => null, 'message' => $e->getMessage()];
                    continue;
                }

                if ($dryRun) {
                    // determine would-insert vs would-update
                    $exists = DB::table('tb_mas_curriculum_subject')
                        ->where('intCurriculumID', $link['intCurriculumID'])
                        ->where('intSubjectID', $link['intSubjectID'])
                        ->exists();
                    if ($exists) $upd++; else $ins++;
                    continue;
                }

                $existing = DB::table('tb_mas_curriculum_subject')
                    ->where('intCurriculumID', $link['intCurriculumID'])
                    ->where('intSubjectID', $link['intSubjectID'])
                    ->first();

                $payload = [
                    'intCurriculumID' => $link['intCurriculumID'],
                    'intSubjectID'    => $link['intSubjectID'],
                    'intYearLevel'    => $link['intYearLevel'],
                    'intSem'          => $link['intSem'],
                ];

                if ($existing) {
                    DB::table('tb_mas_curriculum_subject')
                        ->where('intID', $existing->intID)
                        ->update([
                            'intYearLevel' => $payload['intYearLevel'],
                            'intSem'       => $payload['intSem'],
                        ]);
                    $upd++;
                } else {
                    DB::table('tb_mas_curriculum_subject')->insert($payload);
                    $ins++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = ['sheet' => self::SHEET_SUBJECTS, 'line' => 0, 'key' => null, 'message' => 'DB error: ' . $e->getMessage()];
        }

        return [
            'inserted' => $ins,
            'updated'  => $upd,
            'skipped'  => $skp,
            'errors'   => $errors,
        ];
    }
}
