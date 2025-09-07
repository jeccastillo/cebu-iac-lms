<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class ClasslistStudentImportService
{
    private const SHEET_NAME = 'class-records';

    // Template headers (case-insensitive when parsing)
    private const TEMPLATE_HEADERS = [
        'student_number',        // required: tb_mas_users.strStudentNumber
        'sectionCode',           // required: tb_mas_classlist.sectionCode
        'subjectCode',           // required: tb_mas_subjects.strCode
        'term',                  // required: e.g., "1st 2025-2026 college"
        'campus',                // required: campus name (case-insensitive)
        // Optional fields (safe to write)
        'is_credited_subject',   // 0/1 (default 0)
        'credited_subject_name', // nullable string
        // Optional grades (written if provided and column exists)
        'floatMidtermGrade',
        'floatFinalGrade',
        'floatSemFinalGrade',
        // Optional best-effort mirrors (ignored if schema/columns absent)
        'strUnits',
        'enumStatus',            // default 'act'
        'strRemarks',            // default ''
    ];

    public function generateTemplateXlsx(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(self::SHEET_NAME);

        $c = 1;
        foreach (self::TEMPLATE_HEADERS as $h) {
            $sheet->setCellValueByColumnAndRow($c, 1, $h);
            $sheet->getStyleByColumnAndRow($c, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            $c++;
        }

        // Notes sheet with instructions
        try {
            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);

            $row = 2;
            $notes->setCellValue('A' . $row++, 'Required columns: student_number, sectionCode, subjectCode, term, campus.');
            $notes->setCellValue('A' . $row++, 'Term format: "1st 2025-2026 college" (enumSem + year range + student type).');
            $notes->setCellValue('A' . $row++, 'Campus: exact campus name (case-insensitive).');
            $notes->setCellValue('A' . $row++, 'Upsert key: (student_number + classlist(term + sectionCode + subjectCode[+campus])).');
            $notes->setCellValue('A' . $row++, 'Optional: is_credited_subject (0/1), credited_subject_name (string).');
            $notes->setCellValue('A' . $row++, 'Optional grades: floatMidtermGrade, floatFinalGrade, floatSemFinalGrade (written if provided).');
            $notes->setCellValue('A' . $row++, 'Optional mirrors: strUnits, enumStatus (default act), strRemarks (default empty).');
        } catch (\Throwable $e) {
            // ignore
        }

        return $ss;
    }

    /**
     * Parse uploaded file into generator of normalized rows.
     * Returns: \Generator<['line'=>int,'data'=>array]>
     */
    public function parse(string $path, string $ext): \Generator
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader(($ext === 'xls') ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $ss = $reader->load($path);
            $sheet = $ss->getSheetByName(self::SHEET_NAME) ?: $ss->getSheet(0);

            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            $highestRow = (int) $sheet->getHighestRow();

            // header map
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
                if ($this->rowIsEmpty($row)) continue;
                yield ['line' => $line, 'data' => $row];
            }
            fclose($fh);
        } else {
            throw new RuntimeException('Unsupported file type: ' . $ext);
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
     * Normalize a row into keys + optional write columns.
     * Returns array [$norm, $keys]
     *  - $keys: student_number, sectionCode, subjectCode, term, campus
     *  - $norm: credited_subject_name, is_credited_subject, strUnits, enumStatus, strRemarks
     */
    public function normalizeRow(array $row): array
    {
        $get = function (array $r, array $keys, $default = null) {
            foreach ($keys as $k) {
                $lk = strtolower(trim((string) $k));
                foreach ($r as $rk => $rv) {
                    if (strtolower($rk) === $lk) {
                        return is_string($rv) ? trim($rv) : $rv;
                    }
                }
            }
            return $default;
        };

        $keys = [
            'student_number' => $get($row, ['student_number', 'strStudentNumber'], null),
            'sectionCode'    => $get($row, ['sectionCode', 'section_code', 'section'], null),
            'subjectCode'    => $get($row, ['subjectCode', 'subject_code', 'code'], null),
            'term'           => $get($row, ['term'], null),
            'campus'         => $get($row, ['campus', 'campus_name'], null),
        ];

        $isCred = $get($row, ['is_credited_subject'], null);
        if ($isCred === '' || $isCred === null) $isCred = 0;
        $isCred = (int) $isCred;
        
        $norm = [
            'credited_subject_name' => $get($row, ['credited_subject_name'], null),
            'is_credited_subject'   => in_array($isCred, [0,1], true) ? $isCred : 0,
            'floatMidtermGrade'     => $this->toFloatOrNull($get($row, ['floatMidtermGrade'], null)),
            'floatFinalGrade'       => $this->toFloatOrNull($get($row, ['floatFinalGrade'], null)),
            'floatSemFinalGrade'    => $this->toFloatOrNull($get($row, ['floatSemFinalGrade'], null)),
            'strUnits'              => $get($row, ['strUnits', 'units'], null),
            'enumStatus'            => $get($row, ['enumStatus', 'status'], null),
            'strRemarks'            => $get($row, ['strRemarks', 'remarks'], null),
        ];

        return [$norm, $keys];
    }

    /**
     * Upsert rows by (student_number + classlist(term + sectionCode + subjectCode [+ campus])).
     * Returns [inserted, updated, skipped, errors[]]
     */
    public function upsertRows(iterable $rows, bool $dryRun = false): array
    {
        $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $line = (int) ($item['line'] ?? 0);
                $data = $item['data'] ?? [];

                [$norm, $keys] = $this->normalizeRow($data);

                $studentNumber = (string) ($keys['student_number'] ?? '');
                $sectionCode   = (string) ($keys['sectionCode'] ?? '');
                $subjectCode   = (string) ($keys['subjectCode'] ?? '');
                $termString    = (string) ($keys['term'] ?? '');
                $campusName    = (string) ($keys['campus'] ?? '');

                if (trim($studentNumber) === '' || trim($sectionCode) === '' || trim($subjectCode) === '' || trim($termString) === '' || trim($campusName) === '') {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'REQUIRED', 'message' => 'Missing required fields: student_number, sectionCode, subjectCode, term, campus'];
                    continue;
                }

                // Resolve campus first (required)
                $campusId = $this->resolveCampusIdByName($campusName);
                if (!$campusId) {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'CAMPUS_NOT_FOUND', 'message' => 'Campus not found: ' . $campusName];
                    continue;
                }

                // Resolve or create term by string
                $termId = $this->resolveOrCreateTermByString($termString, $campusId);
                if (!$termId) {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'TERM_NOT_FOUND', 'message' => 'Term not found/created for string: ' . $termString];
                    continue;
                }

                // Resolve subject by code
                $subjectId = $this->resolveSubjectIdByCode($subjectCode);
                if (!$subjectId) {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'SUBJECT_NOT_FOUND', 'message' => 'Subject code not found: ' . $subjectCode];
                    continue;
                }

                // Resolve student by number
                $studentId = $this->resolveStudentIdByNumber($studentNumber);
                if (!$studentId) {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'STUDENT_NOT_FOUND', 'message' => 'Student number not found: ' . $studentNumber];
                    continue;
                }

                // Find classlist by (term + sectionCode + subjectId) optionally constrained by campus_id when present on classlist
                $cl = DB::table('tb_mas_classlist')
                    ->where('strAcademicYear', (int) $termId)
                    ->where('sectionCode', trim($sectionCode))
                    ->where('intSubjectID', (int) $subjectId)
                    ->first();

                if (!$cl) {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'CLASSLIST_NOT_FOUND', 'message' => 'Classlist not found for sectionCode=' . $sectionCode . ', subjectCode=' . $subjectCode . ', term=' . $termString];
                    continue;
                }

                // Optional check: if classlist has campus_id, require it matches resolved campus
                if (isset($cl->campus_id) && $cl->campus_id !== null) {
                    if ((int) $cl->campus_id !== (int) $campusId) {
                        $skp++;
                        $errors[] = ['line' => $line, 'code' => 'CLASSLIST_CAMPUS_MISMATCH', 'message' => 'Classlist campus mismatch for sectionCode=' . $sectionCode];
                        continue;
                    }
                }

                $classlistId = (int) $cl->intID;

                // Existing enrollment?
                $existing = DB::table('tb_mas_classlist_student')
                    ->where('intClassListID', $classlistId)
                    ->where('intStudentID', (int) $studentId)
                    ->first();

                // Defaults and payload
                $enumStatus = $norm['enumStatus'];
                if ($enumStatus === null || $enumStatus === '') $enumStatus = 'act';
                $strRemarks = $norm['strRemarks'];
                if ($strRemarks === null) $strRemarks = '';
                $strUnits = $norm['strUnits'];
                if ($strUnits === null || $strUnits === '') {
                    // fallback from classlist units if available
                    $strUnits = isset($cl->strUnits) ? (string) $cl->strUnits : '0';
                    if ($strUnits === '') $strUnits = '0';
                }

                // Optional grades payload (only when provided AND column exists)
                $grades = [];
                if ($norm['floatMidtermGrade'] !== null && Schema::hasColumn('tb_mas_classlist_student', 'floatMidtermGrade')) {
                    $grades['floatMidtermGrade'] = (float) $norm['floatMidtermGrade'];
                }
                if ($norm['floatFinalGrade'] !== null && Schema::hasColumn('tb_mas_classlist_student', 'floatFinalGrade')) {
                    $grades['floatFinalGrade'] = (float) $norm['floatFinalGrade'];
                }
                if ($norm['floatSemFinalGrade'] !== null && Schema::hasColumn('tb_mas_classlist_student', 'floatSemFinalGrade')) {
                    $grades['floatSemFinalGrade'] = (float) $norm['floatSemFinalGrade'];
                }

                $payload = [
                    'intStudentID'         => (int) $studentId,
                    'intClassListID'       => (int) $classlistId,
                    'intsyID'              => (int) $termId,
                    'enumStatus'           => (string) $enumStatus,
                    'strRemarks'           => (string) $strRemarks,
                    'strUnits'             => (string) $strUnits,
                    'is_credited_subject'  => (int) ($norm['is_credited_subject'] ?? 0),
                    'credited_subject_name'=> $norm['credited_subject_name'] !== '' ? $norm['credited_subject_name'] : null,
                ];
                // Merge grades if any
                if (!empty($grades)) {
                    $payload = array_merge($payload, $grades);
                }

                if ($dryRun) {
                    if ($existing) $upd++; else $ins++;
                    continue;
                }

                if ($existing) {
                    // Update safe subset + optional grades if provided in this import
                    $updateCols = [
                        'enumStatus'           => $payload['enumStatus'],
                        'strRemarks'           => $payload['strRemarks'],
                        'strUnits'             => $payload['strUnits'],
                        'is_credited_subject'  => $payload['is_credited_subject'],
                        'credited_subject_name'=> $payload['credited_subject_name'],
                        'intsyID'              => $payload['intsyID'],
                    ];
                    if (array_key_exists('floatMidtermGrade', $grades)) {
                        $updateCols['floatMidtermGrade'] = $grades['floatMidtermGrade'];
                    }
                    if (array_key_exists('floatFinalGrade', $grades)) {
                        $updateCols['floatFinalGrade'] = $grades['floatFinalGrade'];
                    }
                    if (array_key_exists('floatSemFinalGrade', $grades)) {
                        $updateCols['floatSemFinalGrade'] = $grades['floatSemFinalGrade'];
                    }
                    DB::table('tb_mas_classlist_student')
                        ->where('intCSID', $existing->intCSID)
                        ->update($updateCols);
                    $upd++;
                } else {
                    DB::table('tb_mas_classlist_student')->insert($payload);
                    $ins++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = ['line' => 0, 'code' => 'DB', 'message' => 'DB error: ' . $e->getMessage()];
        }

        return [
            'inserted' => $ins,
            'updated'  => $upd,
            'skipped'  => $skp,
            'errors'   => $errors,
        ];
    }

    private function resolveStudentIdByNumber(string $studentNumber): ?int
    {
        $studentNumber = trim($studentNumber);
        if ($studentNumber === '') return null;
        $row = DB::table('tb_mas_users')
            ->select('intID')
            ->where('strStudentNumber', $studentNumber)
            ->first();
        return ($row && isset($row->intID)) ? (int) $row->intID : null;
    }

    private function resolveSubjectIdByCode(string $code): ?int
    {
        $code = trim($code);
        if ($code === '') return null;
        $row = DB::table('tb_mas_subjects')
            ->whereRaw('LOWER(strCode) = ?', [strtolower($code)])
            ->select('intID')
            ->first();        
        return $row ? (int) $row->intID : null;
    }

    /**
     * Resolve campus by exact name (case-insensitive) to campus_id.
     * Attempts common campus tables known in this system; returns null if not found.
     */
    private function resolveCampusIdByName(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') return null;

        $tables = [
            ['table' => 'tb_mas_campuses', 'id' => 'id',    'name' => 'campus_name'],
            ['table' => 'tb_mas_campus',   'id' => 'intID', 'name' => 'name'],
            ['table' => 'campuses',        'id' => 'id',    'name' => 'name'],
        ];

        foreach ($tables as $t) {
            try {
                $row = DB::table($t['table'])
                    ->select($t['id'] . ' as id', $t['name'] . ' as name')
                    ->whereRaw('LOWER(' . $t['name'] . ') = ?', [strtolower($name)])
                    ->first();
                if ($row && isset($row->id)) {
                    return (int) $row->id;
                }
            } catch (\Throwable $e) {
                // table might not exist; continue
            }
        }

        return null;
    }

    /**
     * Parse a term string like "1st 2025-2026 college" and resolve or create tb_mas_sy row.
     * Returns intID of term or null on failure.
     */
    private function resolveOrCreateTermByString(string $term, ?int $campusId): ?int
    {
        $term = trim(preg_replace('/\s+/', ' ', $term));
        if ($term === '') return null;

        // Extract sem label, years, and student type
        $semLabel = null; $yStart = null; $yEnd = null; $studType = null;

        $parts = explode(' ', $term);
        if (count($parts) >= 3) {
            $semLabel = strtolower(trim($parts[0])); // e.g., 1st/2nd/3rd or numeric
            $yearPart = $parts[1];
            if (preg_match('/^(\d{4})\-(\d{4})$/', $yearPart, $m)) {
                $yStart = (int) $m[1];
                $yEnd = (int) $m[2];
            }
            $studType = strtolower(trim(implode(' ', array_slice($parts, 2))));
        }

        if (!$semLabel || !$yStart || !$yEnd || !$studType) {
            return null;
        }

        $semNum = $this->ordinalToNumber($semLabel);
        $enumSemCandidates = [];
        if ($semNum !== null) $enumSemCandidates[] = (string) $semNum;
        $enumSemCandidates[] = $semLabel;

        // Try to find existing term
        try {
            $q = DB::table('tb_mas_sy')
                ->select('intID', 'enumSem', 'strYearStart', 'strYearEnd', 'term_student_type', 'campus_id')
                ->whereRaw('LOWER(term_student_type) = ?', [strtolower($studType)]);

            $q->where(function ($w) use ($enumSemCandidates) {
                foreach ($enumSemCandidates as $c) {
                    $w->orWhere('enumSem', $c);
                }
            });

            $q->where('strYearStart', $yStart)
              ->where('strYearEnd', $yEnd);

            if ($campusId !== null) {
                $q->where('campus_id', $campusId);
            }

            $existing = $q->first();
            if ($existing && isset($existing->intID)) {
                return (int) $existing->intID;
            }
        } catch (\Throwable $e) {
            // fall through
        }

        // Create a new term row if not existing
        try {
            $insert = [
                'enumSem'           => ($semNum !== null) ? (string) $semNum : $semLabel,
                'strYearStart'      => (int) $yStart,
                'strYearEnd'        => (int) $yEnd,
                'term_student_type' => $studType,
            ];
            if ($campusId !== null) {
                $insert['campus_id'] = (int) $campusId;
            }
            $now = date('Y-m-d H:i:s');
            if (!Schema::hasColumn('tb_mas_sy', 'created_at')) {
                // skip timestamps if not present
            } else {
                $insert['created_at'] = $now;
                $insert['updated_at'] = $now;
            }

            $id = DB::table('tb_mas_sy')->insertGetId($insert);
            return (int) $id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ordinalToNumber(string $s): ?int
    {
        $s = strtolower(trim($s));
        if (preg_match('/^(\d+)(st|nd|rd|th)$/', $s, $m)) {
            return (int) $m[1];
        }
        if (ctype_digit($s)) return (int) $s;
        if ($s === 'first') return 1;
        if ($s === 'second') return 2;
        if ($s === 'third') return 3;
        return null;
    }

    private function toIntOrNull($v): ?int
    {
        if ($v === null || $v === '') return null;
        return (int) $v;
    }

    private function toIntInSet($v, array $allowed, int $default): int
    {
        if ($v === null || $v === '') return $default;
        $n = (int) $v;
        return in_array($n, $allowed, true) ? $n : $default;
    }

    private function toFloatOrNull($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float) $v;
        return null;
    }
}
