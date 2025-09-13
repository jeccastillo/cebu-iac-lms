<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * ClasslistGradesTemplateExport
 *
 * Builds a per-classlist .xlsx template of enrolled students with current grades and an editable "grade" column.
 * - Sheet "grades" columns (exact headers):
 *   intCSID, student_number, last_name, first_name, sectionCode, subjectCode, termLabel, period, current_grade, grade
 * - Sheet "Notes": instructions and rules
 * - Sheet "Options" (only when system-mode is active for the period): allowed values (value, remarks)
 *
 * Period:
 *  - 'midterm'  -> use subject.grading_system_id_midterm; current_grade from floatMidtermGrade
 *  - 'finals'   -> use subject.grading_system_id; current_grade from floatFinalsGrade
 */
class ClasslistGradesTemplateExport
{
    /**
     * Build and return the template Spreadsheet instance.
     *
     * @param int $classlistId
     * @param string $period 'midterm'|'finals'
     * @return Spreadsheet
     */
    public function build(int $classlistId, string $period): Spreadsheet
    {
        $period = strtolower(trim($period));
        if (!in_array($period, ['midterm', 'finals'], true)) {
            $period = 'midterm';
        }

        $cl = $this->fetchClasslist($classlistId);
        if (!$cl) {
            // Build an empty spreadsheet with a notes sheet indicating not found
            $ss = new Spreadsheet();
            $grades = $ss->getActiveSheet();
            $grades->setTitle('grades');
            $this->writeHeaderRow($grades);

            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Classlist not found.');
            $notes->getStyle('A1')->getFont()->setBold(true);
            return $ss;
        }

        $students = $this->fetchStudents($classlistId);

        // Determine grading mode (system vs numeric) for requested period
        $gradingSystemId = ($period === 'midterm')
            ? (int) ($cl->grading_system_id_midterm ?? 0)
            : (int) ($cl->grading_system_id ?? 0);

        $mode = $gradingSystemId > 0 ? 'system' : 'numeric';
        $options = [];
        if ($mode === 'system') {
            $options = $this->fetchGradingItems($gradingSystemId);
        }

        $ss = new Spreadsheet();

        // Build "grades" sheet
        $grades = $ss->getActiveSheet();
        $grades->setTitle('grades');
        $this->writeHeaderRow($grades);

        $row = 2;
        foreach ($students as $s) {
            $current = $this->normalizeCurrentGrade(
                $period === 'midterm' ? ($s->floatMidtermGrade ?? null) : ($s->floatFinalsGrade ?? null)
            );

            // Use explicit data types to avoid PhpSpreadsheet method signature errors and preserve formatting
            $grades->setCellValueExplicit("A{$row}", (string) (int) $s->intCSID, DataType::TYPE_STRING); // keep as string to avoid scientific notation
            $grades->setCellValueExplicit("B{$row}", (string) ($s->strStudentNumber ?? ''), DataType::TYPE_STRING);
            $grades->setCellValueExplicit("C{$row}", (string) ($s->strLastname ?? ''), DataType::TYPE_STRING);
            $grades->setCellValueExplicit("D{$row}", (string) ($s->strFirstname ?? ''), DataType::TYPE_STRING);
            $grades->setCellValueExplicit("E{$row}", (string) ($cl->sectionCode ?? ''), DataType::TYPE_STRING);
            $grades->setCellValueExplicit("F{$row}", (string) ($cl->subject_code ?? ''), DataType::TYPE_STRING);
            $grades->setCellValueExplicit("G{$row}", (string) ($this->buildTermLabel($cl) ?? ''), DataType::TYPE_STRING);
            $grades->setCellValueExplicit("H{$row}", (string) $period, DataType::TYPE_STRING);
            // current_grade shown as empty for legacy "NGS"/50/null
            $grades->setCellValueExplicit("I{$row}", ($current === null ? '' : (string) $current), DataType::TYPE_STRING);
            // Editable grade column initially empty
            $grades->setCellValueExplicit("J{$row}", '', DataType::TYPE_STRING);

            $row++;
        }

        // Autosize columns
        foreach (range('A', 'J') as $col) {
            $grades->getColumnDimension($col)->setAutoSize(true);
        }

        // Build "Notes" sheet
        $notes = $ss->createSheet();
        $notes->setTitle('Notes');
        $this->writeNotes($notes, $period, $mode);

        // Build "Options" sheet for system-mode
        if ($mode === 'system' && !empty($options)) {
            $opt = $ss->createSheet();
            $opt->setTitle('Options');
            $opt->setCellValue('A1', 'value');
            $opt->setCellValue('B1', 'remarks');
            $opt->getStyle('A1:B1')->getFont()->setBold(true);

            $r = 2;
            foreach ($options as $gi) {
                $opt->setCellValue("A{$r}", $gi['value']);
                $opt->setCellValue("B{$r}", $gi['remarks']);
                $r++;
            }
            $opt->getColumnDimension('A')->setAutoSize(true);
            $opt->getColumnDimension('B')->setAutoSize(true);
        }

        return $ss;
    }

    /**
     * Fetch classlist with subject and term info.
     */
    protected function fetchClasslist(int $classlistId): ?object
    {
        $cl = DB::table('tb_mas_classlist as cl')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'cl.strAcademicYear')
            ->where('cl.intID', $classlistId)
            ->select(
                'cl.*',
                's.strCode as subject_code',
                's.strDescription as subject_description',
                's.grading_system_id',
                's.grading_system_id_midterm',
                'sy.intID as syid',
                'sy.enumSem',
                'sy.strYearStart',
                'sy.strYearEnd',
                'sy.term_student_type'
            )
            ->first();

        return $cl ?: null;
    }

    /**
     * Fetch enrolled students for the classlist with user info and current grades.
     */
    protected function fetchStudents(int $classlistId)
    {
        return DB::table('tb_mas_classlist_student as cls')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'cls.intStudentID')
            ->where('cls.intClassListID', $classlistId)
            ->select(
                'cls.intCSID',
                'cls.intStudentID',
                'cls.floatMidtermGrade',
                'cls.floatFinalsGrade',
                'u.strStudentNumber',
                'u.strLastname',
                'u.strFirstname'
            )
            ->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->get();
    }

    /**
     * Fetch grading items for a grading system id.
     * Returns array of ['value' => mixed, 'remarks' => string]
     */
    protected function fetchGradingItems(int $gradingSystemId): array
    {
        $items = DB::table('tb_mas_grading_item')
            ->where('grading_id', $gradingSystemId)
            ->orderBy('value', 'asc')
            ->get();

        $out = [];
        foreach ($items as $r) {
            $out[] = [
                'value'   => $r->value,
                'remarks' => (string) ($r->remarks ?? ''),
            ];
        }
        return $out;
    }

    /**
     * Write header row to the grades sheet.
     */
    protected function writeHeaderRow(Worksheet $sheet): void
    {
        $headers = [
            'A1' => 'intCSID',
            'B1' => 'student_number',
            'C1' => 'last_name',
            'D1' => 'first_name',
            'E1' => 'sectionCode',
            'F1' => 'subjectCode',
            'G1' => 'termLabel',
            'H1' => 'period',
            'I1' => 'current_grade',
            'J1' => 'grade',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    }

    /**
     * Notes sheet content with instructions for users.
     */
    protected function writeNotes(Worksheet $notes, string $period, string $mode): void
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

        $write('Instructions', true);
        $write('- Edit only the "grade" column in the "grades" sheet. Leave blank to skip a student.');
        $write('- period: ' . $period);
        if ($mode === 'numeric') {
            $write('- Mode: numeric (allowed values: 1..100).');
        } else {
            $write('- Mode: grading system (allowed values listed in the "Options" sheet).');
        }
        $write('- Legacy values like "NGS" or 50 (no-grade-submitted) are shown as empty in current_grade.');
        $write('- Upload is restricted by grading windows for faculty; registrar/admin bypass windows.');
        $write('- Columns: intCSID (readonly), student_number, last_name, first_name, sectionCode, subjectCode, termLabel, period, current_grade, grade (editable).');

        // Autosize column A
        $notes->getColumnDimension('A')->setAutoSize(true);
    }

    /**
     * Normalize current grade for display: treat null, '', 'NGS', 50 (or '50') as empty.
     *
     * @param mixed $g
     * @return float|int|string|null
     */
    protected function normalizeCurrentGrade($g)
    {
        if ($g === null) return null;
        if ($g === '') return null;
        if ($g === 'NGS') return null;
        if ($g === 50 || $g === '50') return null;
        return $g;
    }

    /**
     * Build a term label like "1st 2025-2026 college" from classlist/term fields.
     */
    protected function buildTermLabel(object $cl): ?string
    {
        try {
            $sem = (string) ($cl->enumSem ?? '');
            $semLabel = $this->toOrdinalString($sem);
            $ys = (int) ($cl->strYearStart ?? 0);
            $ye = (int) ($cl->strYearEnd ?? 0);
            $studType = (string) ($cl->term_student_type ?? '');
            if ($semLabel && $ys && $ye && $studType) {
                return "{$semLabel} {$ys}-{$ye} {$studType}";
            }
            // Fallback to academic year id
            $ay = (string) ($cl->strAcademicYear ?? '');
            if ($sem || $ay) {
                return trim($sem . ' ' . $ay);
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return null;
    }

    /**
     * Convert a numeric or ordinal-like string to standard ordinal format (1 -> "1st", "first" -> "1st").
     */
    protected function toOrdinalString(string $s): string
    {
        $s = strtolower(trim($s));
        if ($s === '') return '';
        if (ctype_digit($s)) {
            $n = (int) $s;
            return $this->ordinal($n);
        }
        if (preg_match('/^(\d+)(st|nd|rd|th)$/', $s, $m)) {
            return $m[0];
        }
        if ($s === 'first') return '1st';
        if ($s === 'second') return '2nd';
        if ($s === 'third') return '3rd';
        return $s;
    }

    protected function ordinal(int $n): string
    {
        $suffix = 'th';
        if (($n % 100) < 11 || ($n % 100) > 13) {
            switch ($n % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }
        return $n . $suffix;
    }
}
