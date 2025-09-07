<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class ClasslistImportService
{
    private const SHEET_CLASSLISTS = 'classlists';

    // Template headers (case-insensitive when parsing)
    // Accept both numeric term_id and human-readable term string (e.g., "1st 2025-2026 college")
    // Accept campus (string) to resolve campus_id
    private const CLASSLIST_HEADERS = [
        'term_id',       // optional: direct numeric id of tb_mas_sy.intID
        'term',          // optional: "1st 2025-2026 college" -> enumSem, strYearStart, strYearEnd, term_student_type (create tb_mas_sy row if not existing)
        'campus',        // optional: campus name to resolve campus_id on classlist and term creation
        'sectionCode',   // required: unique within term (used with term as upsert key when intID absent)
        'subjectCode',   // required: resolves to tb_mas_subjects.intID via strCode
        'facultyName',   // optional: resolves to tb_mas_faculty.intID (Lastname, Firstname or full_name); ambiguity -> error
        'strUnits',      // optional: default from subject if blank
        'intFinalized',  // optional: default 0 (allowed: 0,1,2)
        'isDissolved',   // optional: default 0 (allowed: 0,1)
        'intID',         // optional: if provided, update by PK
    ];

    public function buildTemplateSpreadsheet(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(self::SHEET_CLASSLISTS);

        $c = 1;
        foreach (self::CLASSLIST_HEADERS as $h) {
            $sheet->setCellValueByColumnAndRow($c, 1, $h);
            $sheet->getStyleByColumnAndRow($c, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            $c++;
        }

        // Notes sheet
        try {
            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $row = 2;
            $notes->setCellValue('A' . $row++, 'Required columns: sectionCode, subjectCode AND either term_id or term.');
            $notes->setCellValue('A' . $row++, 'Term formats:');
            $notes->setCellValue('A' . $row++, ' - term_id: integer ID of term (tb_mas_sy.intID).');
            $notes->setCellValue('A' . $row++, ' - term: "1st 2025-2026 college" (enumSem + year range + term_student_type). If not existing, a term will be created.');
            $notes->setCellValue('A' . $row++, 'Campus: optional campus name. Used to resolve campus_id and associate to classlist and created term (if needed).');
            $notes->setCellValue('A' . $row++, 'Upsert key: intID when provided; otherwise (term + sectionCode).');
            $notes->setCellValue('A' . $row++, 'facultyName: optional; accepts "Lastname, Firstname" or full_name. Ambiguity or not found -> error.');
            $notes->setCellValue('A' . $row++, 'Defaults: intFinalized=0 (0/1/2), isDissolved=0 (0/1). strUnits falls back from subject if blank.');
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
            $sheet = $ss->getSheetByName(self::SHEET_CLASSLISTS) ?: $ss->getSheet(0);

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
     * Normalize a row into classlist data + keys.
     * Returns array [normalized, keys] where keys contains: term_id, sectionCode, intID (optional), subjectCode, facultyName
     */
    public function normalizeRow(array $row): array
    {
        // Accept some aliases
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

        $termId      = $get($row, ['term_id', 'strAcademicYear'], null);
        $termString  = $get($row, ['term'], null);
        $campusName  = $get($row, ['campus', 'campus_name'], null);
        $sectionCode = $get($row, ['sectionCode', 'section_code', 'section'], null);
        $subjectCode = $get($row, ['subjectCode', 'code', 'subject_code'], null);
        $facultyName = $get($row, ['facultyName', 'faculty', 'faculty_name', 'instructor'], null);
        $strUnits    = $get($row, ['strUnits', 'units'], null);
        $intFinalized = $get($row, ['intFinalized', 'finalized'], null);
        $isDissolved  = $get($row, ['isDissolved', 'dissolved'], null);
        $intID        = $get($row, ['intID', 'id'], null);

        // Casts/sanitization occur in upsertRows while knowing subject defaults
        $keys = [
            'term_id'     => $termId,
            'term'        => $termString,
            'campus'      => $campusName,
            'sectionCode' => $sectionCode,
            'subjectCode' => $subjectCode,
            'facultyName' => $facultyName,
            'intID'       => $intID,
        ];
        $norm = [
            'strUnits'     => $strUnits,
            'intFinalized' => $intFinalized,
            'isDissolved'  => $isDissolved,
        ];

        return [$norm, $keys];
    }

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
                // Resolve campus first (optional). If not found, proceed with null campus.
                $campusId = null;
                $campusName = (string) ($keys['campus'] ?? '');
                if ($campusName !== '') {
                    $campusId = $this->resolveCampusIdByName($campusName);
                    // If not found, keep null and continue without skipping the row.
                }

                // Resolve term: prefer numeric term_id; else accept "1st 2025-2026 college"
                $termId = $this->toIntOrNull($keys['term_id'] ?? null);
                $termString = (string) ($keys['term'] ?? '');
                if (!$termId && $termString !== '') {
                    $termId = $this->resolveOrCreateTermByString($termString, $campusId);
                    if (!$termId) {
                        $skp++;
                        $errors[] = ['line' => $line, 'code' => 'TERM_NOT_FOUND', 'message' => 'Term not found/created for string: ' . $termString];
                        continue;
                    }
                }

                $sectionCode = (string) ($keys['sectionCode'] ?? '');
                $subjectCode = (string) ($keys['subjectCode'] ?? '');
                $facultyName = (string) ($keys['facultyName'] ?? '');
                $pkId = $this->toIntOrNull($keys['intID'] ?? null);

                if (!$termId || trim($sectionCode) === '' || trim($subjectCode) === '') {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'REQUIRED', 'message' => 'Missing required fields: term/term_id, sectionCode, subjectCode'];
                    continue;
                }

                $subjectId = $this->resolveSubjectIdByCode($subjectCode);
                if (!$subjectId) {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => 'SUBJECT_NOT_FOUND', 'message' => 'Subject code not found: ' . $subjectCode];
                    continue;
                }

                $facultyId = null;
                if ($facultyName !== '') {
                    $facRes = $this->resolveFacultyIdByName($facultyName);
                    if ($facRes['status'] === 'not_found') {
                        $skp++;
                        $errors[] = ['line' => $line, 'code' => 'FACULTY_NOT_FOUND', 'message' => 'Faculty not found: ' . $facultyName];
                        continue;
                    } elseif ($facRes['status'] === 'ambiguous') {
                        $skp++;
                        $errors[] = ['line' => $line, 'code' => 'FACULTY_AMBIGUOUS', 'message' => 'Faculty name ambiguous: ' . $facultyName];
                        continue;
                    } else {
                        $facultyId = $facRes['id'];
                    }
                }

                // Defaults
                $intFinalized = $this->toIntInSet($norm['intFinalized'] ?? null, [0,1,2], 0);
                $isDissolved  = $this->toIntInSet($norm['isDissolved'] ?? null, [0,1], 0);

                // Units default from subject if not provided
                $strUnits = $norm['strUnits'] ?? null;
                if ($strUnits === '' || $strUnits === null) {
                    $strUnits = $this->getSubjectUnits($subjectId);
                }

                // Prepare DB payload
                $cols = [
                    'intSubjectID'    => (int) $subjectId,
                    'intFacultyID'    => $facultyId, // may be null
                    'strAcademicYear' => (int) $termId,
                    'strUnits'        => (string) $strUnits,
                    'intFinalized'    => (int) $intFinalized,
                    'isDissolved'     => (int) $isDissolved,
                    'sectionCode'     => (string) trim($sectionCode),
                    // Keep legacy-ignored fields blank/neutral per system convention
                    'strClassName'    => '',
                    'strSection'      => '',
                    'sub_section'     => '',
                    'year'            => 0,
                ];
                if ($campusId !== null) {
                    $cols['campus_id'] = (int) $campusId;
                }

                // Determine existing row
                $existing = null;
                if ($pkId && $pkId > 0) {
                    $existing = DB::table('tb_mas_classlist')->where('intID', $pkId)->first();
                    if (!$existing) {
                        $skp++;
                        $errors[] = ['line' => $line, 'code' => 'CLASSLIST_NOT_FOUND', 'message' => 'Classlist not found for intID=' . $pkId];
                        continue;
                    }
                } else {
                    $existing = DB::table('tb_mas_classlist')
                        ->where('strAcademicYear', (int) $termId)
                        ->where('sectionCode', trim($sectionCode))
                        ->first();
                }

                if ($dryRun) {
                    if ($existing) $upd++; else $ins++;
                    continue;
                }

                if ($existing) {
                    DB::table('tb_mas_classlist')->where('intID', $existing->intID)->update($cols);
                    $upd++;
                } else {
                    DB::table('tb_mas_classlist')->insert($cols);
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

    private function getSubjectUnits(int $subjectId): string
    {
        $row = DB::table('tb_mas_subjects')->where('intID', $subjectId)->select('strUnits')->first();
        $u = $row && isset($row->strUnits) ? (string) $row->strUnits : '0';
        return $u === '' ? '0' : $u;
    }

    /**
     * Resolve campus by name (case-insensitive) to campus_id.
     * Tries common campus tables/columns; adjust as needed to your schema.
     */
    private function resolveCampusIdByName(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') return null;

        // Try canonical campuses table if present
        $tables = [
            ['table' => 'tb_mas_campuses', 'id' => 'intID', 'name' => 'name'],
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

        // Extract components: semLabel (e.g., 1st/2nd/3rd), years (YYYY-YYYY), studentType (college/SHS/etc)
        $semLabel = null; $yStart = null; $yEnd = null; $studType = null;

        // Attempt to parse "1st 2025-2026 college"
        $parts = explode(' ', $term);                
        if (count($parts) >= 3) {
            $semLabel = strtolower(trim($parts[0])); // 1st, 2nd, 3rd
            // years may be joined as "2025-2026"
            $yearPart = $parts[1];
            if (preg_match('/^(\d{4})\-(\d{4})$/', $yearPart, $m)) {
                $yStart = (int) $m[1];
                $yEnd = (int) $m[2];
            }
            // student type may have spaces; join remaining parts
            $studType = strtolower(trim(implode(' ', array_slice($parts, 2))));
        }

        if (!$semLabel || !$yStart || !$yEnd || !$studType) {
            return null;
        }

        // Normalize enumSem: reduce to numeric or keep ordinal string; try to match on either variant
        $semNum = $this->ordinalToNumber($semLabel);
        $enumSemCandidates = [];
        if ($semNum !== null) $enumSemCandidates[] = (string) $semNum;
        $enumSemCandidates[] = $semLabel; // e.g., "1st"

        // Try to find existing term
        try {
            $q = DB::table('tb_mas_sy')
                ->select('intID', 'enumSem', 'strYearStart', 'strYearEnd', 'term_student_type', 'campus_id')
                ->whereIn(DB::raw('LOWER(term_student_type)'), [strtolower($studType)]);

            // Add enumSem match (either numeric or ordinal)
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
            // table might not exist; fall through to create attempt
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
            // best-effort defaults for other cols if schema requires (avoid failing insert)
            $now = date('Y-m-d H:i:s');
            if (!array_key_exists('created_at', $insert)) $insert['created_at'] = $now;
            if (!array_key_exists('updated_at', $insert)) $insert['updated_at'] = $now;

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
        // Accept direct numerics too
        if (ctype_digit($s)) return (int) $s;
        // common names
        if ($s === 'first') return 1;
        if ($s === 'second') return 2;
        if ($s === 'third') return 3;
        return null;
    }

    private function toIntOrNull($v): ?int
    {
        if ($v === null || $v === '') return null;
        $n = (int) $v;
        return $n;
    }

    private function toIntInSet($v, array $allowed, int $default): int
    {
        if ($v === null || $v === '') return $default;
        $n = (int) $v;
        return in_array($n, $allowed, true) ? $n : $default;
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
     * Resolve faculty by name. Accepts:
     *  - "Lastname, Firstname"
     *  - exact match against full_name (case-insensitive)
     * Returns ['status' => 'ok', 'id' => int] or ['status' => 'not_found'] or ['status' => 'ambiguous'].
     */
    private function resolveFacultyIdByName(string $name): array
    {
        $name = trim($name);
        if ($name === '') return ['status' => 'not_found'];

        // Try "Lastname, Firstname"
        $last = null; $first = null;
        if (strpos($name, ',') !== false) {
            [$last, $first] = array_map('trim', explode(',', $name, 2));
        }

        $candidates = DB::table('tb_mas_faculty')
            ->select('intID', 'strFirstname', 'strMiddlename', 'strLastname')
            ->get();

        $matched = [];

        foreach ($candidates as $f) {
            $fid = (int) ($f->intID ?? 0);
            $fn = trim((string) ($f->strFirstname ?? ''));
            $mn = trim((string) ($f->strMiddlename ?? ''));
            $ln = trim((string) ($f->strLastname ?? ''));            

            // Exact full_name (case-insensitive)
            // if ($full !== '' && strcasecmp($full, $name) === 0) {
            //     $matched[] = $fid;
            //     continue;
            // }

            // "Lastname, Firstname" strict match ignoring middle name
            if ($last !== null && $first !== null) {
                if (strcasecmp($ln, $last) === 0 && strcasecmp($fn, $first) === 0) {
                    $matched[] = $fid;
                    continue;
                }
            }

            // Fallback: "Firstname Lastname" exact (case-insensitive)
            $fl = trim($fn . ' ' . $ln);
            if ($fl !== '' && strcasecmp($fl, $name) === 0) {
                $matched[] = $fid;
                continue;
            }
        }

        $matched = array_values(array_unique(array_filter($matched)));
        if (count($matched) === 0) {
            return ['status' => 'not_found'];
        }
        if (count($matched) > 1) {
            return ['status' => 'ambiguous'];
        }
        return ['status' => 'ok', 'id' => $matched[0]];
    }
}
