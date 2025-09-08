<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

/**
 * ScheduleImportService
 *
 * Responsibilities:
 * - Generate an XLSX template for schedules
 * - Parse .xlsx/.xls/.csv files into normalized rows
 * - Resolve School Year (intSem), Classlist (by Class Name + Section + Term), Room (by Room Code or TBA)
 * - Upsert tb_mas_room_schedule rows by strScheduleCode (unique identity)
 * - Perform conflict checks consistent with ScheduleController (room/section/faculty conflicts)
 */
class ScheduleImportService
{
    private const SHEET_SCHEDULES = 'schedules';

    // Template headers (case-insensitive on parse)
    private const SCHEDULE_HEADERS = [
        'Code',        // required -> tb_mas_room_schedule.strScheduleCode (upsert identity)
        'Term',        // required -> tb_mas_room_schedule.intSem (tb_mas_sy.intID)
        'Day',         // required -> tb_mas_room_schedule.strDay (1..7)
        'Start',       // required -> tb_mas_room_schedule.dteStart (HH:MM)
        'End',         // required -> tb_mas_room_schedule.dteEnd (HH:MM)
        'Class Type',  // required -> tb_mas_room_schedule.enumClassType (lect|lab)
        'Room Code',   // required -> resolves to intRoomID (TBA -> 99999)
        'Class Name',  // required -> tb_mas_classlist.strClassName (resolve intClasslistID with Section + Term)
        'Section',     // required -> used for classlist lookup (blockSection) and payload
        'Campus ID',   // required -> scope for School Year and Room lookup
    ];

    private const CLASS_TYPES = ['lect', 'lab'];

    /**
     * Build and return the template Spreadsheet instance.
     */
    public function generateTemplateXlsx(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(self::SHEET_SCHEDULES);

        // Header row
        $c = 1;
        foreach (self::SCHEDULE_HEADERS as $h) {
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
            $notes->setCellValue('A' . $row++, 'Required fields: Code, Term, Day (1..7), Start (HH:MM), End (HH:MM), Class Type (lect|lab), Room Code, Class Name, Section, Campus ID.');
            $notes->setCellValue('A' . $row++, 'Upsert identity: Code (update if exists, else insert).');
            $notes->setCellValue('A' . $row++, 'Term must exist in tb_mas_sy and belong to Campus ID.');
            $notes->setCellValue('A' . $row++, 'Classlist is resolved by (Class Name, Section, Term).');
            $notes->setCellValue('A' . $row++, 'Room Code "TBA" maps to intRoomID=99999. Otherwise room must exist in the same Campus ID.');
            $notes->setCellValue('A' . $row++, 'Conflicts are checked (room/time, section/time, faculty/time) and conflicting rows are skipped with errors.');
        } catch (\Throwable $e) {
            // ignore if sheet creation fails
        }

        return $ss;
    }

    /**
     * Parse uploaded file into generator of normalized rows: ['line' => int, 'data' => array].
     *
     * @param string $path
     * @param string $ext one of xlsx|xls|csv
     * @return \Generator<array{line:int,data:array}>
     */
    public function parse(string $path, string $ext): \Generator
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader(($ext === 'xls') ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $ss = $reader->load($path);
            $sheet = $ss->getSheetByName(self::SHEET_SCHEDULES) ?: $ss->getSheet(0);

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
            throw new RuntimeException('Unsupported file extension: ' . $ext);
        }
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Normalize a parsed row into writeable tb_mas_room_schedule columns.
     * Returns [array $cols, array $meta, string $code]
     *
     * $cols keys: strScheduleCode, intSem, strDay, dteStart, dteEnd, enumClassType, blockSection
     * (intRoomID, intClasslistID, intEncoderID are resolved later)
     *
     * $meta keys: room_code, class_name, section, campus_id_raw
     */
    public function normalizeRow(array $row): array
    {
        $cols = [
            'strScheduleCode' => null,            
            'strDay'          => null,
            'dteStart'        => null,
            'dteEnd'          => null,
            'enumClassType'   => null,
            'blockSection'    => null,
            'term'            => null
        ];
        $meta = [
            'room_code'     => null,
            'class_name'    => null,
            'section'       => null,
            'campus_id_raw' => null,
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = is_string($v) ? trim($v) : $v;

            if ($lk === 'code') {
                $cols['strScheduleCode'] = (string) $val;
            } elseif ($lk === 'term') {
                $cols['term'] = (string) $val;
            } elseif ($lk === 'day') {
                $cols['strDay'] = ($val === '' ? null : (int) $val);
            } elseif ($lk === 'start') {
                $cols['dteStart'] = (string) $val;
            } elseif ($lk === 'end') {
                $cols['dteEnd'] = (string) $val;
            } elseif ($lk === 'class type') {
                $cols['enumClassType'] = strtolower((string) $val);
            } elseif ($lk === 'room code') {
                $meta['room_code'] = (string) $val;
            } elseif ($lk === 'class name') {
                $meta['class_name'] = (string) $val;
            } elseif ($lk === 'section') {
                $meta['section'] = (string) $val;
                $cols['blockSection'] = (string) $val;
            } elseif ($lk === 'campus id') {
                $meta['campus_id_raw'] = (string) $val;
            }        
        }

        return [$cols, $meta, (string) ($cols['strScheduleCode'] ?? '')];
    }

    /**
     * Resolve foreign references and validate required values.
     * Mutates $cols to include: intRoomID, intClasslistID, intEncoderID (optional).
     *
     * @param array $cols
     * @param array $meta
     * @param array $contextMeta ['encoder_id' => ?int]
     * @return array additional information ['faculty_id' => ?int]
     */
    public function resolveForeigns(array &$cols, array $meta, array $contextMeta = [],$campusId,$intSem): array
    {
        // Validate required columns
        $code = (string) ($cols['strScheduleCode'] ?? '');
        if ($code === '' || trim($code) === '') {
            throw new RuntimeException('Missing Code');
        }
       

        // Validate Day
        $day = (int) ($cols['strDay'] ?? 0);
        if ($day < 1 || $day > 7) {
            throw new RuntimeException('Invalid Day (must be 1..7)');
        }

        // Validate time format HH:MM
        $start = (string) (date("H:i",strtotime($cols['dteStart'])) ?? '');
        $end   = (string) (date("H:i",strtotime($cols['dteEnd'])) ?? '');

        if ($end <= $start) {
            throw new RuntimeException('End time must be after Start time');
        }

        // Validate class type
        $ctype = (string) ($cols['enumClassType'] ?? '');
        if (!in_array($ctype, self::CLASS_TYPES, true)) {
            throw new RuntimeException('Invalid Class Type (must be lect or lab)');
        }

        // Resolve room
        $roomCode = (string) ($meta['room_code'] ?? '');
        if ($roomCode === '') {
            throw new RuntimeException('Room Code is required');
        }
        if (strtolower($roomCode) === 'tba') {
            $cols['intRoomID'] = 99999;
        } else {
            $rid = DB::table('tb_mas_classrooms')
                ->where('strRoomCode', $roomCode)
                ->where('campus_id', $campusId)
                ->value('intID');
            if ($rid === null) {
                throw new RuntimeException('Room Code not found in campus: ' . $roomCode);
            }
            $cols['intRoomID'] = (int) $rid;
        }

        // Resolve classlist by (Class Name, Section, Term)
        $className = (string) ($meta['class_name'] ?? '');
        $section   = (string) ($meta['section'] ?? '');
        if ($className === '' || $section === '') {
            throw new RuntimeException('Class Name and Section are required');
        }
        $cl = DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('s.strCode', $className)
            ->where('cl.sectionCode', $section)
            ->where('strAcademicYear', $intSem)
            ->select('cl.intID', 'cl.intFacultyID')
            ->first();
        if (!$cl) {
            throw new RuntimeException('Classlist not found for given Class Name, Section and Term');
        }
        $cols['intClasslistID'] = (int) $cl->intID;
        $facultyId = $cl->intFacultyID ? (int) $cl->intFacultyID : null;

        // Optional encoder id
        $encoderId = isset($contextMeta['encoder_id']) ? (int) $contextMeta['encoder_id'] : null;
        if ($encoderId && $encoderId > 0) {
            $cols['intEncoderID'] = $encoderId;
        }

        return ['faculty_id' => $facultyId];
    }

    /**
     * Check room conflicts. Returns array of conflicting rows (may be empty).
     */
    public function checkRoomConflicts(array $data, ?int $excludeId = null): array
    {
        // Ignore TBA room
        if ((int) ($data['intRoomID'] ?? 0) === 99999) {
            return [];
        }

        $q = DB::table('tb_mas_room_schedule as rs')
            ->where(function ($q) use ($data) {
                $q->whereBetween('rs.dteStart', [$data['dteStart'], $data['dteEnd']])
                  ->orWhereBetween('rs.dteEnd', [$data['dteStart'], $data['dteEnd']])
                  ->orWhere(function ($qq) use ($data) {
                      $qq->where('rs.dteStart', '<=', $data['dteStart'])
                         ->where('rs.dteEnd', '>=', $data['dteEnd']);
                  });
            })
            ->where('rs.intRoomID', '!=', 99999);

        if ($excludeId) {
            $q->where('rs.intRoomSchedID', '!=', $excludeId);
        }

        if ((int) $data['strDay'] === 7) {
            $q->where('rs.intRoomID', $data['intRoomID'])
              ->where('rs.intSem', $data['intSem']);
        } else {
            $q->where('rs.strDay', $data['strDay'])
              ->where('rs.intRoomID', $data['intRoomID'])
              ->where('rs.intSem', $data['intSem']);
        }

        return $q->limit(5)->get()->toArray();
    }

    /**
     * Check section conflicts. Returns array of conflicting rows (may be empty).
     */
    public function checkSectionConflicts(array $data, ?int $excludeId = null, ?string $blockSection = null): array
    {
        $q = DB::table('tb_mas_room_schedule as rs')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'rs.intClasslistID')
            ->where(function ($q) use ($data) {
                $q->whereBetween('rs.dteStart', [$data['dteStart'], $data['dteEnd']])
                  ->orWhereBetween('rs.dteEnd', [$data['dteStart'], $data['dteEnd']])
                  ->orWhere(function ($qq) use ($data) {
                      $qq->where('rs.dteStart', '<=', $data['dteStart'])
                         ->where('rs.dteEnd', '>=', $data['dteEnd']);
                  });
            });

        if ($excludeId) {
            $q->where('rs.intRoomSchedID', '!=', $excludeId);
        }

        if ((int) $data['strDay'] === 7) {
            // Mirror controller behavior
            $q->where('rs.intRoomID', $data['intRoomID'])
              ->where('rs.intSem', $data['intSem']);
        } else {
            $q->where('rs.strDay', $data['strDay'])
              ->where('rs.blockSection', $blockSection)
              ->where('rs.intSem', $data['intSem']);
        }

        return $q->limit(5)->get()->toArray();
    }

    /**
     * Check faculty conflicts. Returns array of conflicting rows (may be empty).
     */
    public function checkFacultyConflicts(array $data, ?int $excludeId = null, ?int $facultyId = null): array
    {
        if (!$facultyId) {
            return [];
        }

        $q = DB::table('tb_mas_room_schedule as rs')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'rs.intClasslistID')
            ->where(function ($q) use ($data) {
                $q->whereBetween('rs.dteStart', [$data['dteStart'], $data['dteEnd']])
                  ->orWhereBetween('rs.dteEnd', [$data['dteStart'], $data['dteEnd']])
                  ->orWhere(function ($qq) use ($data) {
                      $qq->where('rs.dteStart', '<=', $data['dteStart'])
                         ->where('rs.dteEnd', '>=', $data['dteEnd']);
                  });
            });

        if ($excludeId) {
            $q->where('rs.intRoomSchedID', '!=', $excludeId);
        }

        if ((int) $data['strDay'] === 7) {
            $q->where('rs.intRoomID', $data['intRoomID'])
              ->where('rs.intSem', $data['intSem']);
        } else {
            $q->where('rs.strDay', $data['strDay'])
              ->where('cl.intFacultyID', $facultyId)
              ->where('rs.intSem', $data['intSem']);
        }

        return $q->limit(5)->get()->toArray();
    }

    /**
     * Upsert rows by strScheduleCode.
     * Returns summary: [totalRows, inserted, updated, skipped, errors[]]
     *
     * @param iterable $rowIter
     * @param bool $dryRun
     * @param bool $skipConflicts
     * @param array $contextMeta e.g. ['encoder_id' => int]
     */
    public function upsertRows(iterable $rowIter, bool $dryRun = false, bool $skipConflicts = true, array $contextMeta = []): array
    {
        $total = 0; $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rowIter as $item) {
                $total++;
                $line = $item['line'] ?? 0;
                $data = $item['data'] ?? [];

                try {
                    [$cols, $meta, $code] = $this->normalizeRow($data);
                    if ($code === '' || trim($code) === '') {
                        throw new RuntimeException('Missing Code');
                    }

                    // Existing schedule by code (unique identity)
                    $existing = DB::table('tb_mas_room_schedule')->where('strScheduleCode', $code)->first();
                    $excludeId = $existing ? (int) $existing->intRoomSchedID : null;

                    // Resolve foreigns and required fields
                    $context = ['encoder_id' => $contextMeta['encoder_id'] ?? null];                                    

                    // if ($dryRun) {
                    //     if ($existing) $upd++; else $ins++;
                    //     continue;
                    // }
                    $campusId = null;
                    $campusName = (string) ($meta['campus_id_raw'] ?? '');
                    if ($campusName !== '') {
                        $campusId = $this->resolveCampusIdByName($campusName);
                        // If not found, keep null and continue without skipping the row.
                    }
                    $termId = null;
                    $termString = (string) ($cols['term'] ?? '');
                    if ($termString !== '') {
                        $termId = $this->resolveOrCreateTermByString($termString, $campusId);
                        if (!$termId) {
                            $skp++;
                            $errors[] = ['line' => $line, 'code' => 'TERM_NOT_FOUND', 'message' => 'Term not found/created for string: ' . $termString];
                            continue;
                        }
                    }
                    $extra = $this->resolveForeigns($cols, $meta, $context,$campusId,$termId);
                    $cols['intSem'] = $termId;
                    // Conflict checks (mirror controller)
                    $roomConf = $this->checkRoomConflicts($cols, $excludeId);
                    $sectionConf = $this->checkSectionConflicts($cols, $excludeId, $cols['blockSection'] ?? null);
                    $facultyConf = $this->checkFacultyConflicts($cols, $excludeId, $extra['faculty_id'] ?? null);

                    if (!empty($roomConf) || !empty($sectionConf) || !empty($facultyConf)) {
                        if ($skipConflicts) {
                            $skp++;
                            $errors[] = [
                                'line' => $line,
                                'code' => $code,
                                'message' => 'Conflict detected',
                                'conflicts' => [
                                    'room' => $roomConf,
                                    'section' => $sectionConf,
                                    'faculty' => $facultyConf,
                                ],
                            ];
                            continue;
                        } else {
                            throw new RuntimeException('Conflict detected');
                        }
                    }

                    // Prepare payload for insert/update
                    $payload = [
                        'intSem'         => $termId,
                        'strDay'         => $cols['strDay'],
                        'dteStart'       => $cols['dteStart'],
                        'dteEnd'         => $cols['dteEnd'],
                        'enumClassType'  => $cols['enumClassType'],
                        'blockSection'   => $cols['blockSection'],
                        'intRoomID'      => $cols['intRoomID'],
                        'intClasslistID' => $cols['intClasslistID'],
                    ];
                    if (!empty($cols['intEncoderID'])) {
                        $payload['intEncoderID'] = $cols['intEncoderID'];
                    }

                    if ($existing) {
                        // Do not allow changing identity code
                        DB::table('tb_mas_room_schedule')
                            ->where('intRoomSchedID', $existing->intRoomSchedID)
                            ->update($payload);
                        $upd++;
                    } else {
                        $payload['strScheduleCode'] = $code;
                        DB::table('tb_mas_room_schedule')->insert($payload);
                        $ins++;
                    }

                } catch (\Throwable $e) {
                    $skp++;
                    $errors[] = [
                        'line' => $line,
                        'code' => $code ?? null,
                        'message' => $e->getMessage(),
                    ];
                    continue;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = ['line' => 0, 'code' => null, 'message' => 'DB error: ' . $e->getMessage()];
        }

        return [
            'totalRows' => $total,
            'inserted'  => $ins,
            'updated'   => $upd,
            'skipped'   => $skp,
            'errors'    => $errors,
        ];
    }

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
        if (ctype_digit($s)) return (int) $s;
        if ($s === 'first') return 1;
        if ($s === 'second') return 2;
        if ($s === 'third') return 3;
        return null;
    }
}
