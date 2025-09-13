<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ClasslistGradesImportService
{
    private const SHEET_NAME = 'grades';

    /**
     * Parse uploaded .xlsx into generator of normalized rows.
     * Returns: \Generator<['line' => int, 'data' => array]>
     * Expects a header row with, at minimum: intCSID, grade (case-insensitive).
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
    }

    private function rowIsEmpty(array $row): bool
    {
        // A row is empty if all values are null/empty string
        foreach ($row as $v) {
            if ($v !== null && $v !== '') return false;
        }
        return true;
    }

    /**
     * Perform upsert of grades based on parsed rows and classlist context.
     *
     * @param iterable $rows generator from parseXlsx()
     * @param object $classlist result of ClasslistService::getClasslistForGrading($id)
     * @param string $period 'midterm'|'finals'
     * @return array [updated, skipped, errors[]]
     */
    public function upsert(iterable $rows, object $classlist, string $period): array
    {
        $updated = 0; $skipped = 0;
        $errors = [];

        $classlistId = (int) ($classlist->intID ?? 0);
        $gradingSystemId = ($period === 'midterm')
            ? (int) ($classlist->grading_system_id_midterm ?? 0)
            : (int) ($classlist->grading_system_id ?? 0);

        $mode = $gradingSystemId > 0 ? 'system' : 'numeric';

        // Preload system items if applicable
        $systemItems = [];
        if ($mode === 'system') {
            $systemItems = $this->loadSystemItems($gradingSystemId);
        }

        foreach ($rows as $item) {
            $line = (int) ($item['line'] ?? 0);
            $data = (array) ($item['data'] ?? []);

            // Extract required fields case-insensitively
            $intCSID = $this->get($data, ['intcsid', 'intCSID', 'csid'], null);
            $grade   = $this->get($data, ['grade'], null);

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

            // Empty grade cell -> skip (no change)
            if ($grade === null || $grade === '') {
                $skipped++;
                continue;
            }

            // Ensure CSID belongs to this classlist
            $row = DB::table('tb_mas_classlist_student')->where('intCSID', $intCSID)->first();
            if (!$row) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'CSID_NOT_FOUND', 'message' => 'intCSID not found: ' . $intCSID];
                continue;
            }
            if ((int) ($row->intClassListID ?? 0) !== $classlistId) {
                $skipped++;
                $errors[] = ['line' => $line, 'code' => 'CSID_CLASSLIST_MISMATCH', 'message' => 'intCSID does not belong to this classlist.'];
                continue;
            }

            $remarksToPersist = null;
            if ($mode === 'numeric') {
                if (!is_numeric($grade)) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'INVALID_NUMERIC', 'message' => 'Non-numeric grade in numeric mode.'];
                    continue;
                }
                $g = (int) $grade;
                if ($g < 1 || $g > 100) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'OUT_OF_RANGE', 'message' => 'Numeric grade out of 1..100 range.'];
                    continue;
                }
                $grade = $g;
            } else {
                // System mode: grade must match a system item 'value'
                if (!array_key_exists((string) $grade, $systemItems)) {
                    $skipped++;
                    $errors[] = ['line' => $line, 'code' => 'INVALID_SYSTEM_VALUE', 'message' => 'Grade not in allowed system items.'];
                    continue;
                }
                $remarksToPersist = $systemItems[(string) $grade];
            }

            $col = $period === 'midterm' ? 'floatMidtermGrade' : 'floatFinalsGrade';
            $dataUpdate = [$col => $grade];

            // Persist remarks for system-mode entries; retain/leave unchanged in numeric fallback
            if ($remarksToPersist !== null) {
                $dataUpdate['strRemarks'] = $remarksToPersist;
            }

            $affected = DB::table('tb_mas_classlist_student')
                ->where('intCSID', $intCSID)
                ->update($dataUpdate);

            if ($affected > 0) {
                $updated += $affected;
            } else {
                // No change (same value); count as skipped
                $skipped++;
            }
        }

        $total = $updated + $skipped;

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'totalRows' => $total,
            'errors'  => $errors,
        ];
    }

    private function loadSystemItems(int $gradingSystemId): array
    {
        $items = DB::table('tb_mas_grading_item')
            ->where('grading_id', $gradingSystemId)
            ->orderBy('value', 'asc')
            ->get();

        $map = [];
        foreach ($items as $it) {
            $val = (string) ($it->value ?? '');
            $map[$val] = (string) ($it->remarks ?? '');
        }
        return $map;
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
}
