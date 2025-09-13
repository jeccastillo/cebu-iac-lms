<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * ClasslistAttendanceMatrixTemplateExport
 *
 * Builds an .xlsx template in "matrix" layout for marking attendance across a date range.
 *
 * Sheet "attendance_matrix" columns:
 *   A: intCSID (readonly identifier)
 *   B: student_number (readonly)
 *   C: last_name (readonly)
 *   D: first_name (readonly)
 *   E..N: one column per date (YYYY-MM-DD). Cells are editable with 0, 1, or empty.
 *
 * Notes sheet explains usage and accepted values.
 */
class ClasslistAttendanceMatrixTemplateExport
{
    /**
     * Build and return the template Spreadsheet instance for a given classlist and date range.
     *
     * @param int $classlistId
     * @param string $start YYYY-MM-DD
     * @param string $end YYYY-MM-DD
     * @param string $period 'midterm'|'finals'
     * @return Spreadsheet
     */
    public function build(int $classlistId, string $start, string $end, string $period): Spreadsheet
    {
        // Normalize and validate inputs
        $start = trim((string) $start);
        $end   = trim((string) $end);
        $period = strtolower(trim((string) $period));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            throw new \InvalidArgumentException('start and end must be in YYYY-MM-DD format.');
        }
        if (!in_array($period, ['midterm', 'finals'], true)) {
            throw new \InvalidArgumentException('period must be midterm or finals.');
        }
        if ($end < $start) {
            throw new \InvalidArgumentException('end date must be on or after start date.');
        }

        // Use ONLY the attendance dates already created for this classlist (within range and period)
        $dates = $this->fetchExistingDates($classlistId, $period, $start, $end);
        if (count($dates) === 0) {
            throw new \InvalidArgumentException('No attendance dates exist for this classlist and period within the specified range.');
        }

        // Load roster for the classlist
        $roster = DB::table('tb_mas_classlist_student as cs')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'cs.intStudentID')
            ->where('cs.intClassListID', $classlistId)
            ->select(
                'cs.intCSID',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname'
            )
            ->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->get();

        $ss = new Spreadsheet();

        // Active sheet: "attendance_matrix"
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('attendance_matrix');

        // Header row
        $this->writeHeaderRow($sheet, $dates);

        // Freeze first row and first 4 columns (so names remain visible)
        $sheet->freezePane('E2');

        // Write roster rows (identifiers only; leave date cells blank for user input)
        $r = 2;
        foreach ($roster as $row) {
            $sheet->setCellValueExplicit("A{$r}", (string) (int) $row->intCSID, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$r}", (string) ($row->strStudentNumber ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$r}", (string) ($row->strLastname ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$r}", (string) ($row->strFirstname ?? ''), DataType::TYPE_STRING);

            // Initialize date cells as empty strings for clarity
            $colIndex = 5; // column 'E'
            foreach ($dates as $_) {
                $sheet->setCellValueExplicitByColumnAndRow($colIndex, $r, '', DataType::TYPE_STRING);
                $colIndex++;
            }

            $r++;
        }

        // Autosize identifier columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Set date columns to a reasonable width
        $colIndex = 5;
        foreach ($dates as $_) {
            $col = $this->columnIndexToString($colIndex);
            $sheet->getColumnDimension($col)->setWidth(12);
            $colIndex++;
        }

        // Notes sheet
        $notes = $ss->createSheet();
        $notes->setTitle('Notes');
        $this->writeNotes($notes, $start, $end, $period);

        return $ss;
    }

    /**
     * Build header row: A1..D1 fixed, E1.. date headers.
     *
     * @param Worksheet $sheet
     * @param array $dates array of YYYY-MM-DD
     * @return void
     */
    protected function writeHeaderRow(Worksheet $sheet, array $dates): void
    {
        $headers = [
            'A1' => 'intCSID',
            'B1' => 'student_number',
            'C1' => 'last_name',
            'D1' => 'first_name',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        // Date headers from column E onwards
        $colIndex = 5; // E
        foreach ($dates as $d) {
            $col = $this->columnIndexToString($colIndex);
            $sheet->setCellValue("{$col}1", $d);
            $colIndex++;
        }

        // Style header row
        $lastHeaderCol = $this->columnIndexToString($colIndex - 1);
        $sheet->getStyle("A1:{$lastHeaderCol}1")->getFont()->setBold(true);
    }

    /**
     * Notes sheet content with instructions for users.
     *
     * @param Worksheet $notes
     * @param string $start
     * @param string $end
     * @param string $period
     * @return void
     */
    protected function writeNotes(Worksheet $notes, string $start, string $end, string $period): void
    {
        $row = 1;
        $write = function (string $text, bool $bold = false) use ($notes, &$row) {
            $cell = 'A' . $row;
            $notes->setCellValue($cell, $text);
            if ($bold) {
                $notes->getStyle($cell)->getFont()->setBold(true);
            }
            $row++;
        };

        $write('Attendance Matrix Import Instructions', true);
        $write('- This template allows editing attendance across a date range in a single sheet.');
        $write('- First row contains date headers (YYYY-MM-DD).');
        $write('- Columns reflect only the attendance dates already created for this classlist (within the selected range and period).');
        $write('- First columns (A-D) are identifiers and must not be changed: intCSID, student_number, last_name, first_name.');
        $write('- For each student/date cell: enter 1 (present), 0 (absent), or leave blank (unset).');
        $write('- Period for this template: ' . $period);
        $write('- Date range: ' . $start . ' to ' . $end);
        $write('- Accepted values (case-insensitive on import):');
        $write('  * Present (true): 1');
        $write('  * Absent (false): 0');
        $write('  * Unset (null):   "" (leave blank)');
        $write('- Remarks are not used in matrix mode; only presence values are imported.');
        $write('- The importer will auto-create and seed dates for this classlist if they do not yet exist.');
        $write('- Ensure that edited dates remain valid (YYYY-MM-DD).');
        $notes->getColumnDimension('A')->setAutoSize(true);
    }

    /**
     * Build inclusive date range array of YYYY-MM-DD strings.
     *
     * @param string $start
     * @param string $end
     * @return array
     */
    protected function getDateRange(string $start, string $end): array
    {
        $out = [];
        $cur = \DateTime::createFromFormat('Y-m-d', $start);
        $to  = \DateTime::createFromFormat('Y-m-d', $end);
        if (!$cur || !$to) {
            return $out;
        }
        $cur->setTime(0, 0, 0);
        $to->setTime(0, 0, 0);
        while ($cur <= $to) {
            $out[] = $cur->format('Y-m-d');
            $cur->modify('+1 day');
        }
        return $out;
    }

    /**
     * Fetch existing attendance dates for a classlist and period within the given range.
     *
     * @param int $classlistId
     * @param string $period
     * @param string $start
     * @param string $end
     * @return array<string>
     */
    protected function fetchExistingDates(int $classlistId, string $period, string $start, string $end): array
    {
        $rows = DB::table('tb_mas_classlist_attendance_date')
            ->where('intClassListID', $classlistId)
            ->where('period', $period)
            ->whereBetween('attendance_date', [$start, $end])
            ->orderBy('attendance_date', 'asc')
            ->pluck('attendance_date')
            ->all();

        // Normalize to strings YYYY-MM-DD (DB returns string/Carbon depending on connection)
        $out = [];
        foreach ($rows as $d) {
            $s = (string) $d;
            // Strict keep only valid Y-m-d
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                $out[] = $s;
            } else {
                // Attempt to parse/format
                $dt = \DateTime::createFromFormat('Y-m-d', $s) ?: new \DateTime($s);
                if ($dt) {
                    $out[] = $dt->format('Y-m-d');
                }
            }
        }
        // Deduplicate just in case
        $out = array_values(array_unique($out));
        return $out;
    }

    /**
     * Convert 1-based column index to Excel column letters (e.g., 1 -> A, 27 -> AA)
     *
     * @param int $index
     * @return string
     */
    protected function columnIndexToString(int $index): string
    {
        $index--; // make it 0-based
        $result = '';
        while ($index >= 0) {
            $result = chr(($index % 26) + 65) . $result;
            $index = intdiv($index, 26) - 1;
        }
        return $result;
    }
}
