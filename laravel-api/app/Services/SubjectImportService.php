<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class SubjectImportService
{
    private const SHEET_SUBJECTS = 'subjects';

    private const SUBJECT_HEADERS = [
        'Code',           // tb_mas_subjects.strCode (required, upsert identity)
        'Description',    // tb_mas_subjects.strDescription
        'Units',          // tb_mas_subjects.strUnits
        'Lab',            // tb_mas_subjects.intLab (0/1)
        'Department',     // tb_mas_subjects.strDepartment
        // Optional numeric fields (best-effort)
        'Lect Hours',     // tb_mas_subjects.intLectHours
        'Tuition Units',  // tb_mas_subjects.strTuitionUnits
        'Lab Classification', // tb_mas_subjects.strLabClassification
    ];

    public function generateTemplateXlsx(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(self::SHEET_SUBJECTS);

        $c = 1;
        foreach (self::SUBJECT_HEADERS as $h) {
            $sheet->setCellValueByColumnAndRow($c, 1, $h);
            $sheet->getStyleByColumnAndRow($c, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            $c++;
        }

        // Notes
        try {
            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $row = 2;
            $notes->setCellValue('A' . $row++, 'Required: Code (unique, case-insensitive).');
            $notes->setCellValue('A' . $row++, 'Optional fields: Description, Units, Lab (0/1), Department, Lect Hours, Tuition Units, Lab Classification.');
            $notes->setCellValue('A' . $row++, 'Upsert identity: Code. If Code exists, row updates; else creates.');
        } catch (\Throwable $e) {}

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
            $sheet = $ss->getSheetByName(self::SHEET_SUBJECTS) ?: $ss->getSheet(0);

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
     * Normalize a row into tb_mas_subjects columns.
     * Returns [array $cols, string $code]
     */
    public function normalizeRow(array $row): array
    {
        $cols = [
            'strCode'            => null,
            'strDescription'     => null,
            'strUnits'           => null,
            'intLab'             => 0,
            'strDepartment'      => null,
            'intLectHours'       => null,
            'strTuitionUnits'    => null,
            'strLabClassification' => null,
        ];

        foreach ($row as $k => $v) {
            $lk = strtolower(trim((string) $k));
            $val = is_string($v) ? trim($v) : $v;

            if ($lk === 'code') $cols['strCode'] = (string) $val;
            elseif ($lk === 'description') $cols['strDescription'] = (string) $val;
            elseif ($lk === 'units') $cols['strUnits'] = ($val === '' ? null : (string) $val);
            elseif ($lk === 'lab') $cols['intLab'] = (int) ($val === '' ? 0 : $val);
            elseif ($lk === 'department') $cols['strDepartment'] = ($val === '' ? null : (string) $val);
            elseif ($lk === 'lect hours') $cols['intLectHours'] = ($val === '' ? null : (int) $val);
            elseif ($lk === 'tuition units') $cols['strTuitionUnits'] = ($val === '' ? null : (string) $val);
            elseif ($lk === 'lab classification') $cols['strLabClassification'] = ($val === '' ? null : (string) $val);
        }

        return [$cols, (string) ($cols['strCode'] ?? '')];
    }

    /**
     * Upsert rows by strCode (case-insensitive).
     * Returns [inserted, updated, skipped, errors[]]
     */
    public function upsertRows(iterable $rows, bool $dryRun = false): array
    {
        $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $line = $item['line'] ?? 0;
                $data = $item['data'] ?? [];

                [$cols, $code] = $this->normalizeRow($data);
                if ($code === '' || trim($code) === '') {
                    $skp++;
                    $errors[] = ['line' => $line, 'code' => null, 'message' => 'Missing Code'];
                    continue;
                }

                // Find existing subject by code (case-insensitive)
                $existing = DB::table('tb_mas_subjects')
                    ->whereRaw('LOWER(strCode) = ?', [strtolower($code)])
                    ->first();

                if ($dryRun) {
                    if ($existing) $upd++; else $ins++;
                    continue;
                }

                if ($existing) {
                    // Update: ignore null-only payload?
                    DB::table('tb_mas_subjects')
                        ->where('intID', $existing->intID)
                        ->update($cols);
                    $upd++;
                } else {
                    DB::table('tb_mas_subjects')->insert($cols);
                    $ins++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errors[] = ['line' => 0, 'code' => null, 'message' => 'DB error: ' . $e->getMessage()];
        }

        return [
            'inserted' => $ins,
            'updated'  => $upd,
            'skipped'  => $skp,
            'errors'   => $errors,
        ];
    }
}
