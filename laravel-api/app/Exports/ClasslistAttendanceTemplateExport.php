<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * ClasslistAttendanceTemplateExport
 *
 * Builds a per-classlist, per-date .xlsx template of attendance rows with an editable "is_present" and "remarks" column.
 * - Sheet "attendance" columns (exact headers):
 *   intCSID, student_number, last_name, first_name, attendance_date, period, is_present, remarks
 * - Sheet "Notes": instructions and accepted values for is_present
 *
 * is_present accepted values (case-insensitive on import):
 *  - true values: "1", "true", "present", "p", "yes"
 *  - false values: "0", "false", "absent", "a", "no"
 *  - null/unset: "", "null", "unset"
 */
class ClasslistAttendanceTemplateExport
{
    /**
     * Build and return the template Spreadsheet instance for a given classlist date.
     *
     * @param int $classlistId
     * @param int $dateId
     * @return Spreadsheet
     */
    public function build(int $classlistId, int $dateId): Spreadsheet
    {
        $ss = new Spreadsheet();

        // Active sheet: "attendance"
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('attendance');
        $this->writeHeaderRow($sheet);

        // Verify date belongs to classlist
        $date = DB::table('tb_mas_classlist_attendance_date')
            ->where('intID', $dateId)
            ->where('intClassListID', $classlistId)
            ->first();

        if (!$date) {
            // Write empty rows but include a Notes sheet with an error message
            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Attendance date not found for this classlist.');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $this->writeGenericNotes($notes);
            // Autosize columns on main sheet
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            return $ss;
        }

        // Fetch attendance rows for the date with user info
        $rows = DB::table('tb_mas_classlist_attendance as a')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'a.intStudentID')
            ->select(
                'a.intCSID',
                'a.is_present',
                'a.remarks',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname'
            )
            ->where('a.intAttendanceDateID', $dateId)
            ->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->get();

        $attendanceDate = (string) ($date->attendance_date ?? '');
        $period = (string) ($date->period ?? '');

        $r = 2;
        foreach ($rows as $row) {
            $is = $row->is_present;
            $isStr = '';
            if ($is === null) {
                $isStr = '';
            } elseif ((int) $is === 1) {
                $isStr = '1';
            } else {
                $isStr = '0';
            }

            // Keep intCSID and student_number as strings to avoid Excel auto-format surprises
            $sheet->setCellValueExplicit("A{$r}", (string) (int) $row->intCSID, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$r}", (string) ($row->strStudentNumber ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$r}", (string) ($row->strLastname ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$r}", (string) ($row->strFirstname ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$r}", $attendanceDate, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$r}", $period, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$r}", $isStr, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$r}", (string) ($row->remarks ?? ''), DataType::TYPE_STRING);

            $r++;
        }

        // Autosize the columns for better readability
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Notes sheet
        $notes = $ss->createSheet();
        $notes->setTitle('Notes');
        $this->writeNotes($notes, $attendanceDate, $period);

        return $ss;
    }

    /**
     * Write header row to the attendance sheet.
     */
    protected function writeHeaderRow(Worksheet $sheet): void
    {
        $headers = [
            'A1' => 'intCSID',
            'B1' => 'student_number',
            'C1' => 'last_name',
            'D1' => 'first_name',
            'E1' => 'attendance_date',
            'F1' => 'period',
            'G1' => 'is_present',
            'H1' => 'remarks',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    }

    /**
     * Notes sheet content with instructions for users.
     */
    protected function writeNotes(Worksheet $notes, string $attendanceDate, string $period): void
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

        $write('Attendance Import Instructions', true);
        $write('- Edit only the "is_present" and "remarks" columns in the "attendance" sheet.');
        $write('- attendance_date: ' . ($attendanceDate ?: '(empty)'));
        $write('- period: ' . ($period ?: '(empty)'));
        $write('- is_present accepted values (case-insensitive):');
        $write('  * Present (true): 1, true, present, p, yes');
        $write('  * Absent (false): 0, false, absent, a, no');
        $write('  * Unset (null):   "", null, unset');
        $write('- Remarks are only persisted when is_present=false (absent).');
        $write('- When is_present=true or unset, remarks will be cleared.');
        $write('- Columns: intCSID (readonly), student_number, last_name, first_name, attendance_date, period, is_present (editable), remarks (editable).');
        $notes->getColumnDimension('A')->setAutoSize(true);
    }

    /**
     * Generic fallback notes when date not found.
     */
    protected function writeGenericNotes(Worksheet $notes): void
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

        $write('Notes', true);
        $write('Attendance date not found for this classlist.');
        $write('Please ensure the selected date exists and try downloading the template again.');
        $notes->getColumnDimension('A')->setAutoSize(true);
    }
}
