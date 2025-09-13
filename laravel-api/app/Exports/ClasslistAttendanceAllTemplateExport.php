<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * ClasslistAttendanceAllTemplateExport
 *
 * Builds an .xlsx template covering ALL attendance dates of a classlist.
 * Sheet "attendance_all" columns (exact headers):
 *   attendance_date, period, intCSID, student_number, last_name, first_name, is_present, remarks
 *
 * is_present output:
 *  - null -> ""
 *  - 1    -> "1"
 *  - 0    -> "0"
 *
 * Notes sheet includes accepted values for import.
 */
class ClasslistAttendanceAllTemplateExport
{
    /**
     * Build and return the template Spreadsheet instance for a given classlist (all dates).
     *
     * @param int $classlistId
     * @return Spreadsheet
     */
    public function build(int $classlistId): Spreadsheet
    {
        $ss = new Spreadsheet();

        // Active sheet: "attendance_all"
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('attendance_all');
        $this->writeHeaderRow($sheet);

        // Fetch all attendance rows across all dates for the classlist
        // Join date table to include attendance_date and period
        $rows = DB::table('tb_mas_classlist_attendance as a')
            ->join('tb_mas_classlist_attendance_date as d', 'd.intID', '=', 'a.intAttendanceDateID')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'a.intStudentID')
            ->where('a.intClassListID', $classlistId)
            ->select(
                'd.attendance_date',
                'd.period',
                'a.intCSID',
                'a.is_present',
                'a.remarks',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname'
            )
            ->orderBy('d.attendance_date', 'desc')
            ->orderBy('d.period', 'asc')
            ->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->get();

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

            $sheet->setCellValueExplicit("A{$r}", (string) ($row->attendance_date ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$r}", (string) ($row->period ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$r}", (string) (int) $row->intCSID, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$r}", (string) ($row->strStudentNumber ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$r}", (string) ($row->strLastname ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$r}", (string) ($row->strFirstname ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$r}", $isStr, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$r}", (string) ($row->remarks ?? ''), DataType::TYPE_STRING);

            $r++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $notes = $ss->createSheet();
        $notes->setTitle('Notes');
        $this->writeNotes($notes);

        return $ss;
    }

    protected function writeHeaderRow(Worksheet $sheet): void
    {
        $headers = [
            'A1' => 'attendance_date',
            'B1' => 'period',
            'C1' => 'intCSID',
            'D1' => 'student_number',
            'E1' => 'last_name',
            'F1' => 'first_name',
            'G1' => 'is_present',
            'H1' => 'remarks',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    }

    protected function writeNotes(Worksheet $notes): void
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

        $write('Attendance All-Dates Import Instructions', true);
        $write('- Edit only "is_present" and "remarks" in the "attendance_all" sheet.');
        $write('- Required columns: attendance_date (YYYY-MM-DD), period (midterm|finals), intCSID.');
        $write('- is_present accepted values (case-insensitive):');
        $write('  * Present (true): 1, true, present, p, yes');
        $write('  * Absent (false): 0, false, absent, a, no');
        $write('  * Unset (null):   "", null, unset');
        $write('- Remarks are only persisted when is_present=false (absent).');
        $write('- When is_present=true or unset, remarks will be cleared.');
        $notes->getColumnDimension('A')->setAutoSize(true);
    }
}
