<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

class SchoolYearImportService
{
    private const SHEET_TERMS = 'school_years';

    // Template headers (order matters)
    private const TERM_HEADERS = [
        // Required
        'enumSem',
        'strYearStart',
        'strYearEnd',
        'campus_id',
        'term_label',
        'term_student_type',
        // Optional
        'midterm_start',
        'midterm_end',
        'final_start',
        'final_end',
        'end_of_submission',
        'start_of_classes',
        'final_exam_start',
        'final_exam_end',
        'viewing_midterm_start',
        'viewing_midterm_end',
        'viewing_final_start',
        'viewing_final_end',
        'endOfApplicationPeriod',
        'reconf_start',
        'reconf_end',
        'ar_report_date_generation',
        'classType',
        'pay_student_visa',
        'is_locked',
        'enumGradingPeriod',
        'enumMGradingPeriod',
        'enumFGradingPeriod',
        'intProcessing',
        'enumStatus',
        'enumFinalized',
    ];

    /**
     * Build template spreadsheet with headers and instructions.
     */
    public function buildTemplateSpreadsheet(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle(self::SHEET_TERMS);

        // Header row
        $c = 1;
        foreach (self::TERM_HEADERS as $h) {
            $sheet->setCellValueByColumnAndRow($c, 1, $h);
            $sheet->getStyleByColumnAndRow($c, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            $c++;
        }

        // Optional sample row (not filled; users to fill-in)
        // Notes sheet
        try {
            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $r = 2;
            $notes->setCellValue('A' . $r++, 'Required columns: enumSem, strYearStart (YYYY), strYearEnd (YYYY), campus_id (numeric), term_label, term_student_type.');
            $notes->setCellValue('A' . $r++, 'Upsert identity (unique key): (strYearStart, strYearEnd, enumSem, campus_id).');
            $notes->setCellValue('A' . $r++, 'If a matching term exists, the row updates; otherwise it inserts.');
            $notes->setCellValue('A' . $r++, 'Optional columns accept ISO date strings (YYYY-MM-DD). Leave blank to keep null.');
            $notes->setCellValue('A' . $r++, 'Allowed enumSem examples: 1st, 2nd, 3rd, 4th, Summer.');
            $notes->setCellValue('A' . $r++, 'term_student_type examples: college, shs, next, others.');
        } catch (\Throwable $e) {
            // ignore
        }

        return $ss;
    }

    /**
     * Parse uploaded file into generator of normalized rows.
     * Yields: ['line' => int, 'data' => array]
     */
    public function parse(string $path, string $ext): \Generator
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader($ext === 'xls' ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $ss = $reader->load($path);
            $sheet = $ss->getSheetByName(self::SHEET_TERMS) ?: $ss->getSheet(0);

            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            $highestRow = (int) $sheet->getHighestRow();

            // Build header map (lowercased)
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
                    if (!isset($header[$c])) {
                        continue;
                    }
                    $key = $header[$c];
                    $val = $sheet->getCellByColumnAndRow($c, $r)->getFormattedValue();
                    $row[$key] = is_string($val) ? trim($val) : $val;
                }
                if ($this->rowIsEmpty($row)) {
                    continue;
                }
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
                    if ($h === '' || !array_key_exists($i, $cols)) {
                        continue;
                    }
                    $row[$h] = $cols[$i];
                }
                if ($this->rowIsEmpty($row)) {
                    continue;
                }
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
            if ($v !== null && $v !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Normalize and validate a row to tb_mas_sy columns.
     * Returns [array $cols, array $errors]
     */
    public function normalizeRow(array $row): array
    {
        $get = function (string $key) use ($row) {
            foreach ($row as $k => $v) {
                if (strtolower(trim((string) $k)) === strtolower($key)) {
                    return is_string($v) ? trim($v) : $v;
                }
            }
            return null;
        };

        $errors = [];

        $enumSem           = (string) ($get('enumSem') ?? $get('enumsem') ?? '');
        $strYearStartRaw   = $get('strYearStart') ?? $get('stryearstart');
        $strYearEndRaw     = $get('strYearEnd') ?? $get('stryearend');
        $campusRaw         = $get('campus_id');
        $termLabel         = (string) ($get('term_label') ?? '');
        $termStudentType   = (string) ($get('term_student_type') ?? '');

        // Required validations
        if ($enumSem === '') {
            $errors[] = 'Missing enumSem';
        }
        $strYearStart = $this->normalizeYear($strYearStartRaw);
        if ($strYearStart === null) {
            $errors[] = 'Invalid strYearStart (YYYY)';
        }
        $strYearEnd = $this->normalizeYear($strYearEndRaw);
        if ($strYearEnd === null) {
            $errors[] = 'Invalid strYearEnd (YYYY)';
        }
        $campusId = $this->toIntOrNull($campusRaw);
        if ($campusId === null) {
            $errors[] = 'Invalid campus_id (numeric required)';
        }
        if ($termLabel === '') {
            $errors[] = 'Missing term_label';
        }
        if ($termStudentType === '') {
            $errors[] = 'Missing term_student_type';
        }

        // Optional dates (normalize to YYYY-MM-DD or null)
        $dates = [
            'midterm_start'             => $this->normalizeDate($get('midterm_start')),
            'midterm_end'               => $this->normalizeDate($get('midterm_end')),
            'final_start'               => $this->normalizeDate($get('final_start')),
            'final_end'                 => $this->normalizeDate($get('final_end')),
            'end_of_submission'         => $this->normalizeDate($get('end_of_submission')),
            'start_of_classes'          => $this->normalizeDate($get('start_of_classes')),
            'final_exam_start'          => $this->normalizeDate($get('final_exam_start')),
            'final_exam_end'            => $this->normalizeDate($get('final_exam_end')),
            'viewing_midterm_start'     => $this->normalizeDate($get('viewing_midterm_start')),
            'viewing_midterm_end'       => $this->normalizeDate($get('viewing_midterm_end')),
            'viewing_final_start'       => $this->normalizeDate($get('viewing_final_start')),
            'viewing_final_end'         => $this->normalizeDate($get('viewing_final_end')),
            'endOfApplicationPeriod'    => $this->normalizeDate($get('endOfApplicationPeriod')),
            'reconf_start'              => $this->normalizeDate($get('reconf_start')),
            'reconf_end'                => $this->normalizeDate($get('reconf_end')),
            'ar_report_date_generation' => $this->normalizeDate($get('ar_report_date_generation')),
        ];

        // Optional numeric flags
        $payStudentVisa = $this->toIntOrNull($get('pay_student_visa'));
        $isLocked       = $this->toIntOrNull($get('is_locked'));
        $intProcessing  = $this->toIntOrNull($get('intProcessing') ?? $get('intprocessing'));

        $cols = [
            // Required / core
            'enumSem'           => $enumSem,
            'strYearStart'      => $strYearStart,
            'strYearEnd'        => $strYearEnd,
            'campus_id'         => $campusId,
            'term_label'        => $termLabel,
            'term_student_type' => $termStudentType,

            // Optional dates
            'midterm_start'             => $dates['midterm_start'],
            'midterm_end'               => $dates['midterm_end'],
            'final_start'               => $dates['final_start'],
            'final_end'                 => $dates['final_end'],
            'end_of_submission'         => $dates['end_of_submission'],
            'start_of_classes'          => $dates['start_of_classes'],
            'final_exam_start'          => $dates['final_exam_start'],
            'final_exam_end'            => $dates['final_exam_end'],
            'viewing_midterm_start'     => $dates['viewing_midterm_start'],
            'viewing_midterm_end'       => $dates['viewing_midterm_end'],
            'viewing_final_start'       => $dates['viewing_final_start'],
            'viewing_final_end'         => $dates['viewing_final_end'],
            'endOfApplicationPeriod'    => $dates['endOfApplicationPeriod'],
            'reconf_start'              => $dates['reconf_start'],
            'reconf_end'                => $dates['reconf_end'],
            'ar_report_date_generation' => $dates['ar_report_date_generation'],

            // Optional misc
            'classType'          => $this->nullIfEmpty($get('classType') ?? $get('classtype')),
            'pay_student_visa'   => $payStudentVisa,
            'is_locked'          => $isLocked,
            'enumGradingPeriod'  => $this->nullIfEmpty($get('enumGradingPeriod') ?? $get('enumgradingperiod')),
            'enumMGradingPeriod' => $this->nullIfEmpty($get('enumMGradingPeriod') ?? $get('enummgradingperiod')),
            'enumFGradingPeriod' => $this->nullIfEmpty($get('enumFGradingPeriod') ?? $get('enumfgradingperiod')),
            'intProcessing'      => $intProcessing,
            'enumStatus'         => $this->nullIfEmpty($get('enumStatus') ?? $get('enumstatus')),
            'enumFinalized'      => $this->nullIfEmpty($get('enumFinalized') ?? $get('enumfinalized')),
        ];

        return [$cols, $errors];
    }

    /**
     * Upsert rows by (strYearStart, strYearEnd, enumSem, campus_id).
     * Returns ['inserted'=>int,'updated'=>int,'skipped'=>int,'errors'=>[]]
     */
    public function upsertRows(iterable $rows, bool $dryRun = false): array
    {
        $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $line = (int) ($item['line'] ?? 0);
                $data = (array) ($item['data'] ?? []);

                [$cols, $rowErrors] = $this->normalizeRow($data);
                if (!empty($rowErrors)) {
                    $skp++;
                    foreach ($rowErrors as $msg) {
                        $errors[] = ['line' => $line, 'code' => null, 'message' => $msg];
                    }
                    continue;
                }

                // Find existing record by composite key
                $existing = DB::table('tb_mas_sy')
                    ->where('strYearStart', $cols['strYearStart'])
                    ->where('strYearEnd', $cols['strYearEnd'])
                    ->where('enumSem', $cols['enumSem'])
                    ->where('campus_id', $cols['campus_id'])
                    ->first();

                if ($dryRun) {
                    if ($existing) { $upd++; } else { $ins++; }
                    continue;
                }

                if ($existing) {
                    DB::table('tb_mas_sy')
                        ->where('intID', $existing->intID)
                        ->update($cols);
                    $upd++;
                } else {
                    DB::table('tb_mas_sy')->insert($cols);
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

    // ----------------------
    // Helpers
    // ----------------------

    private function normalizeYear($v): ?int
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '') return null;
        if (preg_match('/^\d{4}$/', $s)) {
            return (int) $s;
        }
        return null;
    }

    private function normalizeDate($v): ?string
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '') return null;

        // Accept YYYY-MM-DD, YYYY-MM-DD HH:mm[:ss], or ISO strings - prefer to reduce to YYYY-MM-DD
        // Strip time if present
        $s = str_replace('T', ' ', $s);
        $s = preg_replace('/Z$/', '', $s);
        $s = preg_replace('/\.\d+$/', '', $s);

        // Try to capture YYYY-MM-DD
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $s, $m)) {
            $date = $m[1];
            // Replace known invalid '0000-00-00' with null
            if ($date === '0000-00-00') {
                return null;
            }
            return $date;
        }

        return null;
    }

    private function toIntOrNull($v): ?int
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        if ($s === '') return null;
        if (!is_numeric($s)) return null;
        return (int) $s;
    }

    private function nullIfEmpty($v)
    {
        if ($v === null) return null;
        $s = (string) $v;
        return trim($s) === '' ? null : $s;
    }
}
