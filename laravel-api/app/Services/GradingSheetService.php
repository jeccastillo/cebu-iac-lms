<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradingSheetService
{
    public function __construct(private DataFetcherService $fetcher)
    {
    }

    /**
     * Build DTO for grading sheet PDF.
     *
     * @param int $studentId
     * @param int $syid
     * @param string $period 'midterm'|'final'
     * @return array
     */
    public function buildDto(int $studentId, int $syid, string $period, Request $request): array
    {
        $period = strtolower(trim($period)) === 'midterm' ? 'midterm' : 'final';

        $student = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_programs as p', 'u.intProgramID', '=', 'p.intProgramID')
            ->leftJoin('tb_mas_campuses as c', 'u.campus_id', '=', 'c.id')
            ->where('u.intID', $studentId)
            ->select(
                'u.intID as id',
                'u.strStudentNumber as student_number',
                'c.campus_name as campus_name',
                'u.strLastname as last_name',
                'u.strFirstname as first_name',
                'u.strMiddlename as middle_name',
                'u.intProgramID as program_id',
                'p.strProgramCode as program_code',
                'p.strProgramDescription as program_name'
            )
            ->first();

        if (!$student) {
            throw new \InvalidArgumentException('Student not found');
        }

        $sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        if (!$sy) {
            throw new \InvalidArgumentException('Invalid term (syid)');
        }

        $termLabel = sprintf('%s %s-%s', (string)($sy->enumSem ?? ''), (string)$sy->strYearStart, (string)$sy->strYearEnd);

        $data = $this->fetcher->getStudentRecordsByTerm($studentId, (string)$syid, true);
        $terms = is_array($data['terms'] ?? null) ? $data['terms'] : [];
        $records = [];
        if (!empty($terms)) {
            $records = is_array($terms[0]['records'] ?? null) ? $terms[0]['records'] : [];
        }

        $rows = [];
        $gwaNumerator = 0.0;
        $gwaDenominator = 0.0;
        $totalUnitsEarned = 0.0;

        foreach ($records as $r) {
            $code = (string)($r['code'] ?? '');
            $title = (string)($r['description'] ?? '');
            $units = (float)($r['units'] ?? 0);
            $include_gwa = (int)($r['include_gwa'] ?? 0);
            $remarks = (string)($r['remarks'] ?? '');
            $grades = is_array($r['grades'] ?? null) ? $r['grades'] : [];

            $raw = null;
            if ($period === 'midterm') {
                $raw = $grades['midterm'] ?? null;
            } else {
                // final: prefer finals (period grade); fallback to overall final if finals is null
                $raw = ($grades['finals'] ?? null);
                if ($raw === null || $raw === '') {
                    $raw = $grades['final'] ?? null;
                }
            }

            $mapped = $this->mapGradeToDisplayNumeric($raw);
            $display = $mapped['display'];
            $numeric = $mapped['numeric'];

            $passed = $this->isPassed($numeric, $remarks, $display);
            $unitsEarned = $passed ? $units : 0.0;

            if ($include_gwa === 1 && $numeric !== null && is_numeric($numeric)) {
                $gwaNumerator += ((float)$numeric) * $units;
                $gwaDenominator += $units;
            }

            $totalUnitsEarned += $unitsEarned;

            $rows[] = [
                'code'          => $code,
                'title'         => $title,
                'units'         => $units,
                'grade'         => $display,
                'units_earned'  => $unitsEarned,
                'include_gwa'   => $include_gwa,
                'numeric_grade' => $numeric,
                'passed'        => $passed,
            ];
        }

        $gwa = 0.0;
        if ($gwaDenominator > 0.0) {
            $gwa = round($gwaNumerator / $gwaDenominator, 3);
        }

        $generatedBy = $this->resolveGeneratedBy($request);
        $generatedAt = date('Y-m-d H:i a');

        $fullName = $this->formatName($student->last_name ?? '', $student->first_name ?? '', $student->middle_name ?? '');

        return [
            'header' => [
                'school_name' => 'Information & Communications Technology, Inc.',
                'title'       => ($period === 'midterm' ? 'Midterm Grade' : 'Finals Grade') . ' SY ' . (string)$sy->strYearStart . '-' . (string)$sy->strYearEnd . ' ' . (string)($sy->enumSem ?? ''),
                'program'     => $student->program_name ?: ($student->program_code ?: ''),
                'term_label'  => $termLabel,
            ],
            'student' => [
                'number' => (string)($student->student_number ?? ''),
                'name'   => $fullName,
            ],
            'period' => $period,
            'rows' => $rows,
            'summary' => [
                'gwa'          => $gwa,
                'units_earned' => $totalUnitsEarned,
            ],
            'grading_system_notes' => '1.00 (98-100) Excellent; 1.25 (95-97); 1.50 (92-94) Very Good; 1.75 (89-91); 2.00 (86-88); 2.25 (83-85); 2.50 (80-82) Satisfactory; 2.75 (77-79) Fair; 3.00 (75-76); 5.00 (Below 75) Failed; OD (Officially Dropped); UD (Unofficially Dropped); FA (Failure due to Absences); IP (In Progress) for internship only; P (Passed); F (Failed); OW (Officially Withdrawn); UW (Unofficially Withdrawn); NGS (No Grade Submitted)',
            'generated_by' => $generatedBy,
            'generated_at' => $generatedAt,
            'logo_path'    => $this->detectLogoPath($student->campus_name),
        ];
    }

    private function mapGradeToDisplayNumeric($raw): array
    {
        if ($raw === null || $raw === '') {
            return ['display' => '', 'numeric' => null];
        }
        // Numeric (string or float)
        if (is_numeric($raw)) {
            $num = (float)$raw;
            return ['display' => $this->formatGrade($num), 'numeric' => $num];
        }
        $s = strtoupper(trim((string)$raw));
        // Known failing codes → numeric 5.00
        $failCodes = ['F','FA','UD','OD','NGS'];
        if (in_array($s, $failCodes, true)) {
            return ['display' => $s, 'numeric' => 5.00];
        }
        // Pass codes (no numeric) → keep display, exclude from GWA (numeric=null)
        if ($s === 'P' || $s === 'PASSED') {
            return ['display' => $s, 'numeric' => null];
        }
        // Otherwise leave as-is (non-numeric remark), exclude from GWA
        return ['display' => $s, 'numeric' => null];
    }

    private function isPassed(?float $numeric, string $remarks, string $display): bool
    {
        if ($numeric !== null && is_numeric($numeric)) {
            return $numeric <= 3.0 && $numeric > 0;
        }
        $r = strtolower(trim($remarks));
        if ($r !== '' && strpos($r, 'pass') !== false) {
            return true;
        }
        $d = strtolower(trim($display));
        if ($d === 'p' || $d === 'passed') {
            return true;
        }
        return false;
    }

    private function formatGrade(float $g): string
    {
        // Format like "5" or "3.00" depending on decimals
        $s = rtrim(rtrim(number_format($g, 2, '.', ''), '0'), '.');
        return $s;
    }

    private function resolveGeneratedBy(Request $request): ?string
    {
        try {
            $facultyId = $request->header('X-Faculty-ID');
            if ($facultyId !== null && $facultyId !== '' && is_numeric($facultyId)) {
                $f = DB::table('tb_mas_faculty')->where('intID', (int)$facultyId)->first();
                if ($f) {
                    $parts = [];
                    if (!empty($f->strFirstname))  $parts[] = trim((string)$f->strFirstname);
                    if (!empty($f->strMiddlename)) $parts[] = trim((string)$f->strMiddlename);
                    if (!empty($f->strLastname))   $parts[] = trim((string)$f->strLastname);
                    return trim(implode(' ', array_filter($parts)));
                }
            }
        } catch (\Throwable $e) {}
        return null;
    }

    private function formatName(string $last, string $first, string $middle): string
    {
        $full = '';
        $last = trim($last);
        $first = trim($first);
        $middle = trim($middle);
        if ($last !== '') $full .= $last . ', ';
        $full .= $first;
        if ($middle !== '') $full .= ' ' . $middle;
        return trim($full);
    }

    private function detectLogoPath($campusName = "Cebu"): ?string
    {
        // 1) Allow override via config/env
        try {
            $override = config('app.logo_path') ?: env('APP_LOGO_PATH');
            if (is_string($override) && $override !== '') {
                $p = realpath($override) ?: $override;
                if (@file_exists($p)) {
                    return $p;
                }
            }
        } catch (\Throwable $e) {}

        // 2) Attempt to resolve a logo relative to Laravel public/, laravel-api/, and mono-repo root
        $projectRoot = dirname(base_path()); // mono-repo root (one level up from laravel-api)
        $candidates = [
            public_path('assets/img/seal_'.strtolower($campusName).'.png'), // laravel-api/public/assets/...
            base_path('assets/img/seal_'.strtolower($campusName).'.png'),   // laravel-api/assets/...
            $projectRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'seal_'.strtolower($campusName).'.png', // repo-root/assets/...
        ];

        foreach ($candidates as $p) {
            try {
                $rp = is_string($p) ? (realpath($p) ?: $p) : $p;
                if ($rp && @file_exists($rp)) {
                    return $rp;
                }
            } catch (\Throwable $e) {}
        }
        return null;
    }
}
