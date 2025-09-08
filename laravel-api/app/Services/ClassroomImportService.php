<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

/**
 * ClassroomImportService
 *
 * Responsibilities:
 * - Generate an XLSX template for classrooms
 * - Parse .xlsx/.xls/.csv files into normalized rows
 * - Resolve Campus/Campus ID to campus_id
 * - Upsert tb_mas_classrooms rows by (strRoomCode, campus_id) composite key
 */
class ClassroomImportService
{
    private const SHEET_CLASSROOMS = 'classrooms';

    // Template headers (case-insensitive on parse)
    private const CLASSROOM_HEADERS = [
        'Room Code',   // required -> tb_mas_classrooms.strRoomCode
        'Type',        // required -> enumType: lecture|laboratory|hrm|pe
        'Description', // optional -> description
        'Campus',      // optional -> tb_mas_campuses.campus_name (case-insensitive exact), resolves to campus_id
        'Campus ID',   // optional -> campus_id
    ];

    // Allowed enumType values
    private const ROOM_TYPES = ['lecture', 'laboratory', 'hrm', 'pe'];

    /**
     * Build and return the template Spreadsheet instance.
     */
    public function generateTemplateXlsx(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(self::SHEET_CLASSROOMS);

        // Header row
        $c = 1;
        foreach (self::CLASSROOM_HEADERS as $h) {
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
            $notes->setCellValue('A' . $row++, 'Required fields: Room Code, Type.');
            $notes->setCellValue('A' . $row++, 'Type must be one of: lecture, laboratory, hrm, pe.');
            $notes->setCellValue('A' . $row++, 'Campus resolution: Provide either Campus (name, case-insensitive exact) or Campus ID.');
            $notes->setCellValue('A' . $row++, 'If both Campus and Campus ID are provided, they must refer to the same campus.');
            $notes->setCellValue('A' . $row++, 'Upsert identity: (Room Code, Campus ID). If exists, row updates; else inserts.');
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
            $sheet = $ss->getSheetByName(self::SHEET_CLASSROOMS) ?: $ss->getSheet(0);

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
     * Normalize a parsed row into writeable tb_mas_classrooms columns.
     * Returns [array $cols, array $meta]
     * $cols keys: strRoomCode, enumType, description, campus_id (after resolve)
     * $meta keys: campus_name, campus_id_raw
     */
    public function normalizeRow(array $row): array
    {
        $cols = [
            'strRoomCode' => null,
            'enumType'    => null,
            'description' => null,
            // 'campus_id' resolved later
        ];
        $meta = [
            'campus_name'  => null,
            'campus_id_raw'=> null,
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = is_string($v) ? trim($v) : $v;

            if ($lk === 'room code') {
                $cols['strRoomCode'] = (string) $val;
            } elseif ($lk === 'type') {
                $cols['enumType'] = strtolower((string) $val);
            } elseif ($lk === 'description') {
                $cols['description'] = ($val === '' ? null : (string) $val);
            } elseif ($lk === 'campus') {
                $meta['campus_name'] = ($val === '' ? null : (string) $val);
            } elseif ($lk === 'campus id') {
                $meta['campus_id_raw'] = ($val === '' ? null : $val);
            }
        }

        return [$cols, $meta];
    }

    /**
     * Resolve campus to campus_id and validate required fields.
     * Mutates $cols to include campus_id.
     */
    public function resolveForeigns(array &$cols, array $meta): void
    {
        // Validate required fields
        $room = (string) ($cols['strRoomCode'] ?? '');
        $type = (string) ($cols['enumType'] ?? '');
        if ($room === '' || trim($room) === '') {
            throw new RuntimeException('Missing Room Code');
        }
        if ($type === '' || !in_array($type, self::ROOM_TYPES, true)) {
            throw new RuntimeException('Invalid Type (must be one of: ' . implode(', ', self::ROOM_TYPES) . ')');
        }

        // Resolve campus_id using either Campus ID or Campus name
        $campusId = null;
        if ($meta['campus_id_raw'] !== null && $meta['campus_id_raw'] !== '') {
            // Numeric campus id
            $cid = (int) $meta['campus_id_raw'];
            $exists = DB::table('tb_mas_campuses')->where('id', $cid)->exists();
            if (!$exists) {
                throw new RuntimeException('Campus ID not found: ' . $cid);
            }
            $campusId = $cid;
        } elseif ($meta['campus_name'] !== null && $meta['campus_name'] !== '') {
            $cid = DB::table('tb_mas_campuses')
                ->whereRaw('LOWER(campus_name) = ?', [strtolower((string) $meta['campus_name'])])
                ->value('id');
            if ($cid === null) {
                throw new RuntimeException('Campus not found: ' . (string) $meta['campus_name']);
            }
            $campusId = (int) $cid;
        } else {
            throw new RuntimeException('Campus or Campus ID is required');
        }

        $cols['campus_id'] = $campusId;
    }

    /**
     * Upsert rows by (strRoomCode, campus_id) composite key.
     * Returns result summary: [totalRows, inserted, updated, skipped, errors[]]
     *
     * @param iterable $rowIter yields ['line' => int, 'data' => array]
     * @param bool $dryRun if true, validate only without DB writes
     */
    public function upsertRows(iterable $rowIter, bool $dryRun = false): array
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
                    [$cols, $meta] = $this->normalizeRow($data);
                    $this->resolveForeigns($cols, $meta);

                    // Lookup existing by (strRoomCode, campus_id)
                    $existing = DB::table('tb_mas_classrooms')
                        ->where('strRoomCode', $cols['strRoomCode'])
                        ->where('campus_id', $cols['campus_id'])
                        ->first();

                    if ($dryRun) {
                        if ($existing) $upd++; else $ins++;
                        continue;
                    }

                    if ($existing) {
                        // Update existing: only provided columns (room code and campus_id are identity, but allow updating type/description)
                        $payload = $cols;
                        unset($payload['campus_id']); // preserve identity campus_id (do not allow changing campus via update)
                        unset($payload['strRoomCode']); // do not allow changing room code via update
                        if (!empty($payload)) {
                            DB::table('tb_mas_classrooms')
                                ->where('intID', $existing->intID)
                                ->update($payload);
                        }
                        $upd++;
                    } else {
                        // Insert full row
                        DB::table('tb_mas_classrooms')->insert($cols);
                        $ins++;
                    }
                } catch (\Throwable $e) {
                    $skp++;
                    $errors[] = [
                        'line' => $line,
                        'room_code' => isset($data['room code']) ? (string) $data['room code'] : (isset($data['Room Code']) ? (string) $data['Room Code'] : null),
                        'message' => $e->getMessage(),
                    ];
                    continue;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // mark batch failure
            $errors[] = ['line' => 0, 'room_code' => null, 'message' => 'DB error: ' . $e->getMessage()];
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
