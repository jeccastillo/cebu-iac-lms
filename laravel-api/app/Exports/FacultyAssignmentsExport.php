<?php

namespace App\Exports;

use App\Models\Classlist;
use App\Services\ScheduleService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FacultyAssignmentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * @var array<string, mixed>
     */
    protected array $filters;

    protected ScheduleService $scheduleService;

    /**
     * @var array<string,string>
     */
    protected array $scheduleCache = [];

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->scheduleService = app(ScheduleService::class);
    }

    /**
     * Build the query for export with filters.
     *
     * Columns required:
     * - subject code, subject description, section code, term, faculty assigned
     * Order by faculty last name.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $q = Classlist::query()
            ->from('tb_mas_classlist')
            ->leftJoin('tb_mas_subjects as s', 's.intID', '=', 'tb_mas_classlist.intSubjectID')
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'tb_mas_classlist.intFacultyID')
            ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'tb_mas_classlist.strAcademicYear')
            ->select(
                's.strCode as subjectCode',
                's.strDescription as subjectDescription',
                'tb_mas_classlist.sectionCode as sectionCode',
                'tb_mas_classlist.strAcademicYear as term',
                'tb_mas_classlist.intID as classlistId',
                'f.strLastname as facultyLastname',
                'f.strFirstname as facultyFirstname',
                'tb_mas_classlist.intFacultyID as intFacultyID',
                'tb_mas_classlist.isDissolved as isDissolved',
                'sy.enumSem as enumSem',
                'sy.term_label as term_label',
                'sy.strYearStart as strYearStart',
                'sy.strYearEnd as strYearEnd'
            );

        $f = $this->filters;

        // Dissolved guard (default exclude dissolved)
        $includeDissolved = (bool)($f['includeDissolved'] ?? false);
        if (!$includeDissolved) {
            $q->where('tb_mas_classlist.isDissolved', 0);
        }

        // Term (required by controller, but support either key)
        if (isset($f['term']) && $f['term'] !== '' && $f['term'] !== null) {
            $q->where('tb_mas_classlist.strAcademicYear', (int)$f['term']);
        } elseif (isset($f['strAcademicYear']) && $f['strAcademicYear'] !== '') {
            $q->where('tb_mas_classlist.strAcademicYear', (int)$f['strAcademicYear']);
        }

        // Faculty filter (optional). If not provided, we may exclude unassigned unless includeUnassigned=true
        if (isset($f['intFacultyID']) && $f['intFacultyID'] !== '' && $f['intFacultyID'] !== null) {
            $q->where('tb_mas_classlist.intFacultyID', (int)$f['intFacultyID']);
        } else {
            $includeUnassigned = (bool)($f['includeUnassigned'] ?? false);
            if (!$includeUnassigned) {
                $q->whereNotNull('tb_mas_classlist.intFacultyID');
            }
        }

        // Optional filters: sectionCode (LIKE), subjectCode (LIKE)
        if (!empty($f['sectionCode'])) {
            $needle = '%' . str_replace(['%','_'], ['\%','\_'], trim((string)$f['sectionCode'])) . '%';
            $q->where('tb_mas_classlist.sectionCode', 'like', $needle);
        }
        if (!empty($f['subjectCode'])) {
            $needle = '%' . str_replace(['%','_'], ['\%','\_'], trim((string)$f['subjectCode'])) . '%';
            $q->where('s.strCode', 'like', $needle);
        }

        // Order by faculty last name then first name, then subject code and section
        $q->orderBy('f.strLastname', 'asc')
          ->orderBy('f.strFirstname', 'asc')
          ->orderBy('s.strCode', 'asc')
          ->orderBy('tb_mas_classlist.sectionCode', 'asc');

        return $q;
    }

    /**
     * Column headers for the spreadsheet.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Subject Code', 'Subject Description', 'Section Code', 'Term', 'Schedule', 'Faculty Assigned'];
    }

    /**
     * Map a row to export columns.
     *
     * @param object $row
     * @return array<int, scalar|null>
     */
    public function map($row): array
    {
        $faculty = '';
        if (!empty($row->facultyLastname) || !empty($row->facultyFirstname)) {
            $last = (string)($row->facultyLastname ?? '');
            $first = (string)($row->facultyFirstname ?? '');
            $faculty = trim($last . ', ' . $first, ', ');
        }

        // Human-readable term label (e.g., "1st sem 2025-2026")
        $enumSem = trim((string)($row->enumSem ?? ''));
        $termLbl = trim((string)($row->term_label ?? ''));
        $yStart  = trim((string)($row->strYearStart ?? ''));
        $yEnd    = trim((string)($row->strYearEnd ?? ''));

        $termText = '';
        if (($enumSem !== '' || $termLbl !== '') && $yStart !== '' && $yEnd !== '') {
            // Prefer enumSem (e.g., "1st Sem") else term_label (e.g., "1st Term")
            $base = $enumSem !== '' ? $enumSem : $termLbl;
            // Normalize to "1st sem"/"1st term"
            $termText = trim(strtolower($base) . ' ' . $yStart . '-' . $yEnd);
        } else {
            // Fallback to numeric term id if metadata is missing
            $termText = (string)($row->term ?? '');
        }

        // Schedule summary text for the classlist
        $scheduleText = '';
        $classlistId = (int)($row->classlistId ?? 0);
        $syid = (int)($row->term ?? 0);
        if ($classlistId > 0 && $syid > 0) {
            $cacheKey = $classlistId . ':' . $syid;
            if (!array_key_exists($cacheKey, $this->scheduleCache)) {
                $map = $this->scheduleService->getClasslistSchedulesForTerm([$classlistId], $syid);
                if (isset($map[$classlistId])) {
                    $summary = $this->scheduleService->summarizeSchedules($map[$classlistId]);
                    $this->scheduleCache[$cacheKey] = (string)($summary['text'] ?? '');
                } else {
                    $this->scheduleCache[$cacheKey] = '';
                }
            }
            $scheduleText = $this->scheduleCache[$cacheKey];
        }

        return [
            $row->subjectCode ?? '',
            $row->subjectDescription ?? '',
            $row->sectionCode ?? '',
            $termText,
            $scheduleText,
            $faculty,
        ];
    }
}
