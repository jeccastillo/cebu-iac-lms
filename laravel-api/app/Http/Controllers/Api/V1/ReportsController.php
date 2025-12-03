<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\EnrolledStudentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Services\GradingSheetService;
use App\Services\Pdf\GradingSheetPdf;
use App\Services\Pdf\EnrollmentStatisticsPdf;
use App\Services\Pdf\StudentTranscriptPdf;
use App\Models\TranscriptRequest;
use App\Models\PaymentDescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use App\Services\PaymentDetailAdminService;

class ReportsController extends Controller
{
    /**
     * GET /api/v1/reports/enrolled-students/export?syid=INT
     *
     * Validates the selected term (syid) and returns an Excel file containing
     * enrolled students for that term. Enrollment filter: r.intAYID = :syid AND r.intROG = 1.
     * Program code is derived via COALESCE(r.current_program, u.intProgramID).
     *
     * Guarded by middleware in routes: role:registrar,admin
     */
    public function enrolledStudentsExport(Request $request)
    {
        $payload = $request->validate([
            'syid' => 'required|integer',
        ]);

        $syid = (int) $payload['syid'];
        $filename = 'enrolled-students-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new EnrolledStudentsExport($syid), $filename);
    }

    public function dailyEnrollmentSummary(Request $request)
    {
        $payload = $request->validate([
            'syid' => 'required|integer',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        $syid = (int) $payload['syid'];
        $dateFrom = $payload['date_from'] ?? null;
        $dateTo = $payload['date_to'] ?? null;

        $dataBuckets = [];

        $totals = [
            'freshman' => 0,
            'transferee' => 0,
            'second' => 0,
            'continuing' => 0,
            'shiftee' => 0,
            'returning' => 0,
            'total' => 0,
        ];

        // Build base query
        $query = DB::table('tb_mas_registration')
            ->select(DB::raw('DATE(date_enrolled) as d'), 'enumStudentType')
            ->where('intAYID', $syid)
            ->where('enrollment_status', 'enrolled');

        // Apply optional date filters
        if (!empty($dateFrom)) {
            $query->where('date_enrolled', '>=', $dateFrom . ' 00:00:00');
        }
        if (!empty($dateTo)) {
            // Inclusive end date via exclusive upper-bound at next day 00:00:00
            $endExclusive = date('Y-m-d', strtotime($dateTo . ' +1 day'));
            $query->where('date_enrolled', '<', $endExclusive . ' 00:00:00');
        }

        $rows = $query->orderBy('d')->get();

        $mapType = function (?string $t): ?string {
            if ($t === null) return null;
            $x = strtolower(trim($t));
            if ($x === 'freshman' || $x === 'new') return 'freshman';
            if ($x === 'transferee') return 'transferee';
            if ($x === 'second degree' || $x === 'second') return 'second';
            if ($x === 'continuing' || $x === 'old') return 'continuing';
            if ($x === 'shiftee') return 'shiftee';
            if ($x === 'returning' || $x === 'returnee') return 'returning';
            // ignore unknowns
            return null;
        };

        if (!empty($dateFrom) && !empty($dateTo)) {
            // Pre-initialize buckets for provided range
            $endExclusive = date('Y-m-d', strtotime($dateTo . ' +1 day'));
            $period = new \DatePeriod(new \DateTime($dateFrom), new \DateInterval('P1D'), new \DateTime($endExclusive));
            foreach ($period as $dt) {
                $d = $dt->format('Y-m-d');
                $dataBuckets[$d] = [
                    'date' => date('M j, Y', strtotime($d)),
                    'freshman' => 0,
                    'transferee' => 0,
                    'second' => 0,
                    'continuing' => 0,
                    'shiftee' => 0,
                    'returning' => 0,
                    'total' => 0,
                ];
            }
        }

        foreach ($rows as $row) {
            $dateKey = $row->d;

            // If no pre-initialized bucket (no range provided), create on the fly
            if (!isset($dataBuckets[$dateKey])) {
                $dataBuckets[$dateKey] = [
                    'date' => date('M j, Y', strtotime($dateKey)),
                    'freshman' => 0,
                    'transferee' => 0,
                    'second' => 0,
                    'continuing' => 0,
                    'shiftee' => 0,
                    'returning' => 0,
                    'total' => 0,
                ];
            }

            $bucketKey = $mapType($row->enumStudentType);
            if ($bucketKey === null) {
                continue;
            }
            $dataBuckets[$dateKey][$bucketKey] += 1;
            $dataBuckets[$dateKey]['total'] += 1;

            $totals[$bucketKey] += 1;
            $totals['total'] += 1;
        }

        // Reindex to sequential array (preserving order as iterated)
        $data = array_values($dataBuckets);

        return response()->json([
            'syid' => $syid,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'data' => $data,
            'totals' => $totals,
        ]);
    }

    /**
     * GET /api/v1/reports/grading-sheet/pdf?student_id=INT&amp;syid=INT&amp;period=midterm|final
     * Streams a grading sheet PDF inline.
     * Guarded by role middleware in routes: registrar, faculty_admin, admin.
     */
    public function gradingSheetPdf(Request $request, GradingSheetService $service)
    {
        $payload = $request->validate([
            'student_id' => 'required|integer',
            'syid'       => 'required|integer',
            'period'     => 'required|string|in:midterm,final',
        ]);

        $dto = $service->buildDto((int) $payload['student_id'], (int) $payload['syid'], (string) $payload['period'], $request);

        $renderer = app(GradingSheetPdf::class);
        $content = $renderer->render($dto);

        $filename = 'grading-sheet-' . now()->format('Ymd-His') . '.pdf';
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /api/v1/reports/enrollment-statistics/pdf?syid=INT
     * Streams an Enrollment Statistics PDF inline (new tab).
     * Groups enrolled students by Program and by Year extracted from student numbers.
     * Years are displayed in descending order; only years present in data are included.
     */
    public function enrollmentStatisticsPdf(Request $request)
    {
        $payload = $request->validate([
            'syid' => 'required|integer',
        ]);

        $syid = (int) $payload['syid'];

        // Fetch minimal term label (3rd Term/SY 2024-2025)
        $sy = DB::table('tb_mas_sy')->select('strYearStart','strYearEnd','enumSem','campus_id')->where('intID', $syid)->first();
        $termLabel = '';
        if ($sy) {
            $ys = isset($sy->strYearStart) ? (string)$sy->strYearStart : '';
            $ye = isset($sy->strYearEnd)   ? (string)$sy->strYearEnd   : '';
            $sem= isset($sy->enumSem)      ? (string)$sy->enumSem      : '';
            $termLabel = trim(($sem !== '' ? $sem . ' ' : '') . 'Term/SY ' . $ys . '-' . $ye);
        }

        // Resolve campus address for selected term (if campus_id present)
        $campusAddress = null;
        if ($sy && property_exists($sy, 'campus_id') && $sy->campus_id !== null) {
            $campus = DB::table('tb_mas_campuses')->select('address', 'campus_name')->where('id', $sy->campus_id)->first();
            if ($campus && isset($campus->address)) {
                $addr = trim((string) $campus->address);
                if ($addr !== '') {
                    $campusAddress = $addr;
                }
            }
        }

        // Pull enrolled students for term with resolved program description/code
        $rows = DB::table('tb_mas_registration as r')
            ->join('tb_mas_users as u', 'u.intID', '=', 'r.intStudentID')
            ->leftJoin('tb_mas_programs as rp', 'rp.intProgramID', '=', 'r.current_program')
            ->leftJoin('tb_mas_programs as up', 'up.intProgramID', '=', 'u.intProgramID')
            ->where('r.intAYID', $syid)
            ->where('r.enrollment_status', 'enrolled')
            ->select(
                'u.strStudentNumber as student_number',
                DB::raw('COALESCE(rp.strProgramDescription, up.strProgramDescription, rp.strProgramCode, up.strProgramCode) as program_name')
            )
            ->get();

        // Aggregate by program and year parsed from student_number
        $progMap = [];            // program => ['counts'=>[year=>n], 'total'=>n]
        $yearsSet = [];           // year => true
        $totalsByYear = [];       // year => n
        $grand = 0;

        foreach ($rows as $r) {
            $sn = isset($r->student_number) ? (string) $r->student_number : '';
            $prog = trim((string) ($r->program_name ?? ''));
            if ($prog === '') $prog = '—';

            // Extract first 4-digit year starting with 20
            $yr = null;
            if (preg_match('/(20\d{2})/', $sn, $m)) {
                $yr = (int) $m[1];
            } else {
                // Skip rows with no recognizable year per instruction preference
                continue;
            }

            if (!isset($progMap[$prog])) {
                $progMap[$prog] = ['counts' => [], 'total' => 0];
            }
            if (!isset($progMap[$prog]['counts'][$yr])) {
                $progMap[$prog]['counts'][$yr] = 0;
            }
            $progMap[$prog]['counts'][$yr] += 1;
            $progMap[$prog]['total'] += 1;

            $yearsSet[$yr] = true;
            if (!isset($totalsByYear[$yr])) $totalsByYear[$yr] = 0;
            $totalsByYear[$yr] += 1;

            $grand += 1;
        }

        // Determine descending years (only those present)
        $years = array_keys($yearsSet);
        rsort($years, SORT_NUMERIC);

        // Build ordered rows (program ascending)
        $programs = array_keys($progMap);
        sort($programs, SORT_NATURAL|SORT_FLAG_CASE);

        $tableRows = [];
        foreach ($programs as $p) {
            $counts = [];
            foreach ($years as $y) {
                $counts[$y] = (int) ($progMap[$p]['counts'][$y] ?? 0);
            }
            $tableRows[] = [
                'program' => $p,
                'counts'  => $counts,
                'total'   => (int) ($progMap[$p]['total'] ?? 0),
            ];
        }

        // Normalize totals by year (ensure all years present)
        $normTotals = [];
        foreach ($years as $y) {
            $normTotals[$y] = (int) ($totalsByYear[$y] ?? 0);
        }

        $dto = [
            'title'      => 'Enrollment Statistics',
            'term_label' => $termLabel,
            'campus_address' => $campusAddress,
            'years'      => $years,
            'rows'       => $tableRows,
            'totals'     => [
                'by_year' => $normTotals,
                'grand'   => (int) $grand,
            ],
        ];

        $renderer = app(EnrollmentStatisticsPdf::class);
        $content  = $renderer->render($dto);

        $filename = 'enrollment-statistics-' . now()->format('Ymd-His') . '.pdf';
        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            // inline -> open in new tab
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * POST /api/v1/reports/students/{studentId}/transcript
     * Streams a Transcript/Copy of Grades PDF inline.
     * Body: { date_issued, remarks?, prepared_by?, verified_by?, registrar_signatory?, signatory?, type: transcript|copy, term_ids: [int...] }
     * Guarded by role middleware in routes.
     */
    public function studentTranscriptPdf(Request $request, int $studentId)
    {
        $payload = $request->validate([
            'date_issued' => 'required|string',
            'remarks' => 'nullable|string',
            'prepared_by' => 'nullable|string',
            'verified_by' => 'nullable|string',
            'registrar_signatory' => 'nullable|string',
            'signatory' => 'nullable|string',
            'type' => 'required|string|in:transcript,copy,Transcript,Copy,copy_of_grades,copy_of_grade',
            'term_ids' => 'required|array|min:1',
            'term_ids.*' => 'integer',
        ]);

        // Normalize type
        $typeRaw = strtolower((string)$payload['type']);
        $type = ($typeRaw === 'copy' || str_contains($typeRaw, 'copy')) ? 'copy' : 'transcript';

        // Resolve student core info + program (tolerate missing columns)
        $u = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_programs as p', 'u.intProgramID', '=', 'p.intProgramID')
            ->leftJoin('tb_mas_applicant_data as a', 'u.intID', '=', 'a.user_id')
            ->leftJoin('tb_mas_applicant_journey as j', 'a.id', '=', 'j.applicant_data_id')            
            ->where('u.intID', $studentId)
            ->where('j.remarks', "Status was changed to Enrolled")
            ->select(
                'u.intID',
                'u.strStudentNumber',
                'u.strFirstname',
                'u.strMiddlename',
                'u.strLastname',
                'u.enumGender',
                'u.strCitizenship',                
                'u.dteBirthDate',                
                'u.date_of_graduation',
                'j.log_date',
                'u.nstp_serial',
                'p.strProgramDescription',
                'p.strProgramCode'
            )
            ->first();

        if (!$u) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Build name "Last, First Middle"
        $first = (string)($u->strFirstname ?? '');
        $middle= (string)($u->strMiddlename ?? '');
        $last  = (string)($u->strLastname ?? '');
        $name  = trim(($last ?: '') . ($last && $first ? ', ' : '') . ($first ?: '') . ($middle ? (' ' . $middle) : ''));

        $program = (string)($u->strProgramDescription ?? $u->strProgramCode ?? '');
        $sn = (string)($u->strStudentNumber ?? '');

        $student = [
            'name' => $name,
            'program' => $program,
            'student_number' => $sn,
            'dteBirthDate' => $this->fmtDate($u->dteBirthDate ?? null),            
            'gender' => (string)($u->enumGender ?? ''),
            'citizenship' => (string)($u->strCitizenship ?? ''),                        
            'date_of_admission' => $this->fmtDate($u->log_date ?? null) ?: 'XXXXXX',
            'date_of_graduation' => $this->fmtDate($u->date_of_graduation ?? null) ?: 'XXXXXXX',
            'nstp_serial_no' => (string)($u->nstp_serial ?? 'XXXXXXX'),
        ];

        // Fetch records for selected terms
        $termIds = array_map('intval', $payload['term_ids']);
        sort($termIds, SORT_NUMERIC);

        $terms = [];
        foreach ($termIds as $tid) {
            $rows = DB::table('tb_mas_classlist_student as cls')
                ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
                ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
                ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'cl.strAcademicYear')
                ->where('cls.intStudentID', $studentId)
                ->where('cls.is_credited_subject', 0)
                ->where('cl.strAcademicYear', $tid)
                ->orderBy('s.strCode', 'asc')
                ->select(
                    's.strCode as code',
                    's.strDescription as description',
                    's.strUnits as units',
                    'cls.strRemarks as remarks',
                    'cls.floatFinalGrade as final',
                    'sy.enumSem',
                    'sy.strYearStart',
                    'sy.strYearEnd'
                )
                ->get();

            $label = '';
            $sy = DB::table('tb_mas_sy')->where('intID', $tid)->first();
            if ($sy) {
                $label = sprintf('SY %s-%s %s', $sy->strYearStart ?? '', $sy->strYearEnd ?? '', ($sy->enumSem ?? ''));
            }

            $records = [];
            foreach ($rows as $r) {
                $records[] = [
                    'code' => $r->code,
                    'description' => $r->description,
                    'units' => $r->units,
                    'remarks' => $r->remarks,
                    'grades' => ['final' => $r->final],
                ];
            }

            $terms[] = [
                'syid' => $tid,
                'label' => $label,
                'records' => $records,
            ];
        }

        $dto = [
            'type' => $type,
            'date_issued' => $this->fmtDateTime($payload['date_issued']),
            'remarks' => (string)($payload['remarks'] ?? ''),
            'prepared_by' => (string)($payload['prepared_by'] ?? ''),
            'verified_by' => (string)($payload['verified_by'] ?? ''),
            'registrar_signatory' => (string)($payload['registrar_signatory'] ?? ''),
            'signatory' => (string)($payload['signatory'] ?? ''),
            'student' => $student,
            // Legend and note can be optionally overridden by request; default inside renderer
            'legend' => $request->input('legend'),
            'note'   => $request->input('note'),
            'terms'  => $terms,
        ];

        // Save transcript billing record
        try {
            $firstTermId = $termIds[0] ?? null;

            // Prefer globally selected term (billing_term_id) if provided by client; fallback to first selected term
            $billingTermIdParam = $request->input('billing_term_id');
            $termForBilling = null;
            if ($billingTermIdParam !== null && $billingTermIdParam !== '') {
                $termForBilling = (int) $billingTermIdParam;
            } else {
                $termForBilling = $firstTermId;
            }

            // Resolve campus using the term chosen for billing
            $campusId = null;
            if ($termForBilling !== null) {
                $syRow = DB::table('tb_mas_sy')->select('campus_id')->where('intID', $termForBilling)->first();
                if ($syRow && isset($syRow->campus_id)) {
                    $campusId = (int) $syRow->campus_id;
                }
            }

            $descName = ($type === 'copy') ? 'Copy of Grades' : 'Transcript of Records';
            $pdQuery = PaymentDescription::query()->whereRaw('LOWER(name) = ?', [strtolower($descName)]);
            $pd = null;
            if ($campusId !== null) {
                $pd = (clone $pdQuery)->where('campus_id', $campusId)->first();
                if (!$pd) {
                    $pd = (clone $pdQuery)->whereNull('campus_id')->first();
                }
            } else {
                $pd = (clone $pdQuery)->whereNull('campus_id')->first();
                if (!$pd) {
                    $pd = $pdQuery->first();
                }
            }
            $amount = $pd ? (float) ($pd->amount ?? 0) : null;
            $pdId = $pd ? (int) $pd->intID : null;

            $facultyId = $request->header('X-Faculty-ID');
            $facultyId = is_numeric($facultyId) ? (int) $facultyId : null;

            TranscriptRequest::create(array(
                'student_id'             => (int) $u->intID,
                'student_number'         => $sn,
                'type'                   => $type,
                'payment_description_id' => $pdId,
                'amount'                 => $amount,
                'term_ids'               => $termIds,
                'campus_id'              => $campusId,
                'date_issued'            => $this->fmtDateTime($payload['date_issued']),
                'prepared_by'            => (string)($payload['prepared_by'] ?? ''),
                'verified_by'            => (string)($payload['verified_by'] ?? ''),
                'registrar_signatory'    => (string)($payload['registrar_signatory'] ?? ''),
                'signatory'              => (string)($payload['signatory'] ?? ''),
                'remarks'                => (string)($payload['remarks'] ?? ''),
                'created_by_faculty_id'  => $facultyId,
            ));

            // Also create a Student Billing item for this transcript.
            // If amount is not configured, still create a zero-amount billing with a remark.
            if ($termForBilling !== null) {
                try {
                    /** @var \App\Services\StudentBillingService $billingSvc */
                    $billingSvc = app(\App\Services\StudentBillingService::class);
                    $amtToBill = ($amount !== null) ? (float) $amount : 0.0;
                    $remarksOut = 'Auto-generated for ' . $descName . ($amount === null ? ' (no amount configured)' : '');
                    $billingSvc->create(array(
                        'intStudentID' => (int) $u->intID,
                        'syid'         => (int) $termForBilling,
                        'description'  => $descName,
                        'amount'       => $amtToBill,
                        'posted_at'    => $this->fmtDateTime($payload['date_issued']),
                        'remarks'      => $remarksOut,
                    ), $facultyId);
                } catch (\Throwable $e2) {
                    // Ignore billing creation failure; still proceed to render PDF
                }
            }
        } catch (\Throwable $e) {
            // Do not block PDF generation on logging failure
        }


        /** @var StudentTranscriptPdf $renderer */
        $renderer = app(StudentTranscriptPdf::class);
        $content  = $renderer->render($dto);

        $filename = ($type === 'copy' ? 'copy-of-grades-' : 'transcript-') . ($sn !== '' ? $sn . '-' : '') . now()->format('Ymd-His') . '.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    protected function resolveTranscriptPayment(?int $campusId, string $type): array
    {
        $descName = ($type === 'copy') ? 'Copy of Grades' : 'Transcript of Records';
        $q = PaymentDescription::query()->whereRaw('LOWER(name) = ?', [strtolower($descName)]);
        $pd = null;
        if ($campusId !== null) {
            $pd = (clone $q)->where('campus_id', $campusId)->first();
            if (!$pd) $pd = (clone $q)->whereNull('campus_id')->first();
        } else {
            $pd = (clone $q)->whereNull('campus_id')->first();
            if (!$pd) $pd = $q->first();
        }
        return [
            'name' => $descName,
            'payment_description_id' => $pd ? (int)$pd->intID : null,
            'amount' => $pd ? (float)($pd->amount ?? 0) : null,
        ];
    }

    public function transcriptFee(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_id' => 'required|integer',
            'type'       => 'required|string',
            'term_id'    => 'nullable|integer',
        ]);

        $campusId = null;
        $termId = $payload['term_id'] ?? null;
        if ($termId !== null) {
            $syRow = DB::table('tb_mas_sy')->select('campus_id')->where('intID', (int)$termId)->first();
            if ($syRow && isset($syRow->campus_id)) {
                $campusId = (int) $syRow->campus_id;
            }
        }

        $typeRaw = strtolower((string) $payload['type']);
        $type = ($typeRaw === 'copy' || str_contains($typeRaw, 'copy')) ? 'copy' : 'transcript';

        $res = $this->resolveTranscriptPayment($campusId, $type);

        return response()->json([
            'success' => true,
            'data' => [
                'description' => $res['name'],
                'amount'      => $res['amount'],
                'payment_description_id' => $res['payment_description_id'],
                'campus_id'   => $campusId,
            ],
        ]);
    }

    public function listTranscriptRequests(Request $request, int $studentId): JsonResponse
    {
        $rows = DB::table('transcript_requests')
            ->where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();


        $items = [];
        foreach ($rows as $r) {
            // Normalize type and description
            $typeRaw = strtolower((string)($r->type ?? 'transcript'));
            $type = (str_contains($typeRaw, 'copy')) ? 'copy' : 'transcript';
            $descName = ($type === 'copy') ? 'Copy of Grades' : 'Transcript of Records';

            // Decode term_ids and resolve first term
            $termIds = json_decode((string)($r->term_ids ?? '[]'), true) ?: [];
            $firstTermId = isset($termIds[0]) ? (int) $termIds[0] : null;

            // Prefer globally selected term if provided via query (?term_id=)
            $termFilter = $request->query('term_id');
            $termForBilling = null;
            if ($termFilter !== null && $termFilter !== '') {
                $termForBilling = (int) $termFilter;
            } else {
                $termForBilling = $firstTermId;
            }

            // Resolve billing per request, not per type:
            // 1) Try to match a billing by posted_at ~= date_issued (±1 minute) and description across ANY term.
            // 2) Else, try to match by amount (when present) and description across ANY term (latest row).
            // 3) Else, fallback to the legacy term+description check.
            $hasBilling = false;
            $billingId = null;

            // Normalize date_issued window when present
            $dateIssued = isset($r->date_issued) ? (string) $r->date_issued : null;
            if ($dateIssued) {
                $from = date('Y-m-d H:i:s', strtotime($dateIssued . ' -1 minute'));
                $to   = date('Y-m-d H:i:s', strtotime($dateIssued . ' +1 minute'));

                $billByDate = DB::table('tb_mas_student_billing')
                    ->select('intID', 'posted_at')
                    ->where('intStudentID', (int) $studentId)
                    ->whereRaw('LOWER(description) = ?', [strtolower($descName)])
                    ->whereNotNull('posted_at')
                    ->whereBetween('posted_at', [$from, $to])
                    ->orderBy('intID', 'desc')
                    ->first();

                if ($billByDate) {
                    $hasBilling = true;
                    $billingId = (int) $billByDate->intID;
                }
            }

            // Amount-based match across any term (when amount present) if not yet found
            if ($billingId === null && isset($r->amount) && $r->amount !== null) {
                $amt = (float) $r->amount;
                $billByAmt = DB::table('tb_mas_student_billing')
                    ->select('intID')
                    ->where('intStudentID', (int) $studentId)
                    ->whereRaw('LOWER(description) = ?', [strtolower($descName)])
                    ->where('amount', $amt)
                    ->orderBy('intID', 'desc')
                    ->first();
                if ($billByAmt) {
                    $hasBilling = true;
                    $billingId = (int) $billByAmt->intID;
                }
            }

            // Fallback: legacy term+description check retained as last resort
            if ($billingId === null && $termForBilling !== null) {
                $bill = DB::table('tb_mas_student_billing')
                    ->select('intID')
                    ->where('intStudentID', (int) $studentId)
                    ->where('syid', $termForBilling)
                    ->whereRaw('LOWER(description) = ?', [strtolower($descName)])
                    ->first();
                if ($bill) {
                    $hasBilling = true;
                    $billingId = (int) $bill->intID;
                }
            }

            // Determine paid per request by billing linkage for the chosen term
            $paid = false;
            $paidInvNos = [];
            if ($billingId !== null) {
                $invRows = DB::table('tb_mas_invoices as i')
                    ->select('i.invoice_number', 'i.amount_total')
                    ->where('i.intStudentID', (int) $studentId)
                    ->where('i.billing_id', $billingId)
                    ->get();

                foreach ($invRows as $inv) {
                    $row = [
                        'invoice_number' => isset($inv->invoice_number) ? (int) $inv->invoice_number : null,
                        'amount_total'   => isset($inv->amount_total) ? (float) $inv->amount_total : null,
                    ];
                    if ($this->isInvoiceFullyPaid($row)) {
                        $paid = true;
                        if ($row['invoice_number'] !== null) {
                            $paidInvNos[] = (string) $row['invoice_number'];
                        }
                    }
                }
            }

            $items[] = [
                'id' => (int) $r->id,
                'created_at' => (string) $r->created_at,
                'date_issued' => isset($r->date_issued) ? (string) $r->date_issued : null,
                'type' => (string) $r->type,
                'amount' => isset($r->amount) ? (float) $r->amount : null,
                'payment_description_id' => isset($r->payment_description_id) ? (int) $r->payment_description_id : null,
                'term_ids' => $termIds,
                'has_billing' => $hasBilling,
                'billing_id' => $billingId,
                'paid' => $paid,
                'paid_invoice_numbers' => $paidInvNos,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function reprintTranscript(Request $request, int $studentId, int $requestId)
    {
        $rec = DB::table('transcript_requests')->where('id', $requestId)->where('student_id', $studentId)->first();
        if (!$rec) {
            return response()->json(['success' => false, 'message' => 'Transcript request not found'], 404);
        }

        // Normalize type
        $typeRaw = strtolower((string)$rec->type);
        $type = ($typeRaw === 'copy' || str_contains($typeRaw, 'copy')) ? 'copy' : 'transcript';

        // Resolve student core info + program (same as studentTranscriptPdf)
        $u = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_programs as p', 'u.intProgramID', '=', 'p.intProgramID')
            ->leftJoin('tb_mas_applicant_data as a', 'u.intID', '=', 'a.user_id')
            ->leftJoin('tb_mas_applicant_journey as j', 'a.id', '=', 'j.applicant_data_id')            
            ->where('u.intID', $studentId)
            ->where('j.remarks', "Status was changed to Enrolled")
            ->select(
                'u.intID',
                'u.strStudentNumber',
                'u.strFirstname',
                'u.strMiddlename',
                'u.strLastname',
                'u.enumGender',
                'u.strCitizenship',                
                'u.dteBirthDate',                
                'u.date_of_graduation',
                'j.log_date',
                'u.nstp_serial',
                'p.strProgramDescription',
                'p.strProgramCode'
            )
            ->first();

        if (!$u) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $first = (string)($u->strFirstname ?? '');
        $middle= (string)($u->strMiddlename ?? '');
        $last  = (string)($u->strLastname ?? '');
        $name  = trim(($last ?: '') . ($last && $first ? ', ' : '') . ($first ?: '') . ($middle ? (' ' . $middle) : ''));
        $program = (string)($u->strProgramDescription ?? $u->strProgramCode ?? '');
        $sn = (string)($u->strStudentNumber ?? '');

        $student = [
            'name' => $name,
            'program' => $program,
            'student_number' => $sn,
            'dteBirthDate' => $this->fmtDate($u->dteBirthDate ?? null),            
            'gender' => (string)($u->enumGender ?? ''),
            'citizenship' => (string)($u->strCitizenship ?? ''),                        
            'date_of_admission' => $this->fmtDate($u->log_date ?? null) ?: 'XXXXXX',
            'date_of_graduation' => $this->fmtDate($u->date_of_graduation ?? null) ?: 'XXXXXXX',
            'nstp_serial_no' => (string)($u->nstp_serial ?? 'XXXXXXX'),
        ];

        // Terms from saved record
        $termIds = array_map('intval', json_decode((string)$rec->term_ids, true) ?: array());
        sort($termIds, SORT_NUMERIC);

        $terms = [];
        foreach ($termIds as $tid) {
            $rows = DB::table('tb_mas_classlist_student as cls')
                ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
                ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
                ->leftJoin('tb_mas_sy as sy', 'sy.intID', '=', 'cl.strAcademicYear')
                ->where('cls.intStudentID', $studentId)
                ->where('cls.is_credited_subject', 0)
                ->where('cl.strAcademicYear', $tid)
                ->orderBy('s.strCode', 'asc')
                ->select(
                    's.strCode as code',
                    's.strDescription as description',
                    's.strUnits as units',
                    'cls.strRemarks as remarks',
                    'cls.floatFinalGrade as final',
                    'sy.enumSem',
                    'sy.strYearStart',
                    'sy.strYearEnd'
                )
                ->get();

            $label = '';
            $sy = DB::table('tb_mas_sy')->where('intID', $tid)->first();
            if ($sy) {
                $label = sprintf('SY %s-%s %s', $sy->strYearStart ?? '', $sy->strYearEnd ?? '', ($sy->enumSem ?? ''));
            }

            $records = [];
            foreach ($rows as $r) {
                $records[] = [
                    'code' => $r->code,
                    'description' => $r->description,
                    'units' => $r->units,
                    'remarks' => $r->remarks,
                    'grades' => ['final' => $r->final],
                ];
            }

            $terms[] = [
                'syid' => $tid,
                'label' => $label,
                'records' => $records,
            ];
        }

        $dto = [
            'type' => $type,
            'date_issued' => $this->fmtDateTime($rec->date_issued ?? null),
            'remarks' => (string)($rec->remarks ?? ''),
            'prepared_by' => (string)($rec->prepared_by ?? ''),
            'verified_by' => (string)($rec->verified_by ?? ''),
            'registrar_signatory' => (string)($rec->registrar_signatory ?? ''),
            'signatory' => (string)($rec->signatory ?? ''),
            'student' => $student,
            'legend' => $request->input('legend'),
            'note'   => $request->input('note'),
            'terms'  => $terms,
        ];

        /** @var StudentTranscriptPdf $renderer */
        $renderer = app(StudentTranscriptPdf::class);
        $content  = $renderer->render($dto);

        $filename = ($type === 'copy' ? 'copy-of-grades-' : 'transcript-') . ($sn !== '' ? $sn . '-' : '') . now()->format('Ymd-His') . '-reprint.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function createTranscriptBilling(Request $request, int $studentId, int $requestId): JsonResponse
    {
        $rec = DB::table('transcript_requests')
            ->where('id', $requestId)
            ->where('student_id', $studentId)
            ->first();

        if (!$rec) {
            return response()->json(['success' => false, 'message' => 'Transcript request not found'], 404);
        }

        // Determine description and first term
        $typeRaw = strtolower((string)($rec->type ?? 'transcript'));
        $type = (str_contains($typeRaw, 'copy')) ? 'copy' : 'transcript';
        $descName = ($type === 'copy') ? 'Copy of Grades' : 'Transcript of Records';

        $termIds = json_decode((string)($rec->term_ids ?? '[]'), true) ?: [];
        $firstTermId = isset($termIds[0]) ? (int)$termIds[0] : null;

        // Prefer globally selected term if provided (POST body: term_id)
        $termIdParam = $request->input('term_id');
        $termId = null;
        if ($termIdParam !== null && $termIdParam !== '') {
            $termId = (int) $termIdParam;
        } else {
            $termId = $firstTermId;
        }
        if ($termId === null) {
            return response()->json(['success' => false, 'message' => 'Missing term_ids on transcript request'], 422);
        }

        // Check existing billing
        $existing = DB::table('tb_mas_student_billing')
            ->select('intID')
            ->where('intStudentID', (int)$rec->student_id)
            ->where('syid', $termId)
            ->whereRaw('LOWER(description) = ?', [strtolower($descName)])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'already_exists' => true,
                'billing_id' => (int) $existing->intID,
            ]);
        }

        // Create billing
        try {
            /** @var \App\Services\StudentBillingService $billingSvc */
            $billingSvc = app(\App\Services\StudentBillingService::class);
            $actor = $request->header('X-Faculty-ID');
            $actorId = is_numeric($actor) ? (int) $actor : null;
            $amt = isset($rec->amount) ? (float) $rec->amount : 0.0;

            $row = $billingSvc->create([
                'intStudentID' => (int) $rec->student_id,
                'syid'         => $termId,
                'description'  => $descName,
                'amount'       => $amt,
                'posted_at'    => $this->fmtDateTime($rec->date_issued ?? null),
                'remarks'      => 'Auto-generated for ' . $descName . ' (via History/Create Billing)',
            ], $actorId);

            return response()->json([
                'success' => true,
                'data' => $row,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create billing: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function isInvoiceFullyPaid(array $inv): bool
    {
        // Strict rule: invoice is paid only when payment_details sum for its invoice_number
        // (status='Paid') is greater than or equal to invoice.amount_total. Ignore invoice.status.
        $invoiceNumber = isset($inv['invoice_number']) ? (int) $inv['invoice_number'] : null;
        $total = isset($inv['amount_total']) ? (float) $inv['amount_total'] : null;

        if ($invoiceNumber === null || $invoiceNumber <= 0 || $total === null) {
            return false;
        }

        try {
            if (!Schema::hasTable('payment_details')) {
                return false;
            }
            if (!Schema::hasColumn('payment_details', 'invoice_number') ||
                !Schema::hasColumn('payment_details', 'status') ||
                !Schema::hasColumn('payment_details', 'subtotal_order')) {
                return false;
            }

            $paidSum = (float) DB::table('payment_details')
                ->where('invoice_number', $invoiceNumber)
                ->where('status', 'Paid')
                ->sum('subtotal_order');

            return ($paidSum >= ($total - 0.00001));
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function anyFullyPaidInvoicesForType(int $studentId, string $type): array
    {
        $type = strtolower($type);
        $descName = ($type === 'copy') ? 'Copy of Grades' : 'Transcript of Records';
        $paidInvNos = [];

        try {
            $invoices = DB::table('tb_mas_student_billing as sb')
                ->join('tb_mas_invoices as i', function ($j) {
                    $j->on('i.billing_id', '=', 'sb.intID');
                })
                ->where('sb.intStudentID', $studentId)
                ->where('i.intStudentID', $studentId)
                ->whereRaw('LOWER(sb.description) = ?', [strtolower($descName)])
                ->select('i.invoice_number', 'i.amount_total', 'i.status')
                ->get();

            foreach ($invoices as $inv) {
                $row = [
                    'invoice_number' => isset($inv->invoice_number) ? (int) $inv->invoice_number : null,
                    'amount_total'   => isset($inv->amount_total) ? (float) $inv->amount_total : null,
                    'status'         => isset($inv->status) ? (string) $inv->status : '',
                ];
                if ($this->isInvoiceFullyPaid($row)) {
                    if ($row['invoice_number'] !== null) {
                        $paidInvNos[] = (string) $row['invoice_number'];
                    } else {
                        $paidInvNos[] = 'status:Paid';
                    }
                }
            }
        } catch (\Throwable $e) {
            // fail-open: treat as unpaid when errors occur
        }

        return [
            'paid' => !empty($paidInvNos),
            'invoice_numbers' => $paidInvNos,
        ];
    }

    private function fmtDate($v): ?string
    {
        if (!$v) return null;
        try {
            return date('Y-m-d', strtotime((string)$v));
        } catch (\Throwable $e) { return null; }
    }

    private function fmtDateTime($v): ?string
    {
        if (!$v) return null;
        try {
            // Accept 'YYYY-MM-DDTHH:mm' or any strtotime-compatible
            $s = str_replace('T', ' ', (string)$v);
            return date('Y-m-d H:i:s', strtotime($s));
        } catch (\Throwable $e) { return null; }
    }

    /**
     * GET /api/v1/reports/invoices
     * Params:
     *  - date_from: Y-m-d (required)
     *  - date_to:   Y-m-d (required, >= date_from)
     *  - campus_id?: int
     *  - type?: string
     *  - status?: string
     *
     * Filters by payment_details.or_date range (inclusive) when column exists; otherwise falls back to detected date column.
     * Includes only invoices that have at least one Paid payment_details row within the range.
     * Returns normalized rows for the Invoice Reports table.
     */
    public function invoiceReports(Request $request)
    {
        $payload = $request->validate([
            'date_from' => 'required|date_format:Y-m-d',
            'date_to'   => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'campus_id' => 'nullable|integer',
            'type'      => 'nullable|string',
            'status'    => 'nullable|string',
        ]);

        $dateFrom = (string) $payload['date_from'];
        $dateTo   = (string) $payload['date_to'];
        $campusId = array_key_exists('campus_id', $payload) ? (int) $payload['campus_id'] : null;
        $type     = array_key_exists('type', $payload) ? (string) $payload['type'] : null;
        $invStat  = array_key_exists('status', $payload) ? (string) $payload['status'] : null;

        /** @var PaymentDetailAdminService $pdSvc */
        $pdSvc = app(PaymentDetailAdminService::class);
        $cols  = $pdSvc->detectColumns();

        if (!($cols['exists'] ?? false) || empty($cols['number_invoice'])) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'meta'    => ['count' => 0, 'date_from' => $dateFrom, 'date_to' => $dateTo],
            ]);
        }

        // Prefer 'or_date' specifically for this report
        $dateCol = Schema::hasColumn($cols['table'], 'or_date') ? 'or_date' : ($cols['date'] ?: null);

        // 1) Collect earliest (date, method) per invoice_number for Paid rows in the date range
        $base = DB::table($cols['table'] . ' as pd')
            ->whereNotNull('pd.' . $cols['number_invoice']);

        if (!empty($cols['status'])) {
            $base->where('pd.' . $cols['status'], 'Paid');
        }

        if ($dateCol) {
            // Inclusive range
            $base->where('pd.' . $dateCol, '>=', $dateFrom)
                 ->where('pd.' . $dateCol, '<=', $dateTo);
        }

        $agg = (clone $base)
            ->select([
                'pd.' . $cols['number_invoice'] . ' as invoice_number',
                $dateCol
                    ? DB::raw('MIN(pd.' . $dateCol . ') as invoice_date')
                    : DB::raw('MIN(pd.' . $cols['id'] . ') as invoice_date'),
            ])
            ->groupBy('pd.' . $cols['number_invoice'])
            ->orderBy('pd.' . $cols['number_invoice'], 'asc')
            ->get();

        $invNos = [];
        $firstByInv = []; // invNo => ['date'=>..., 'method'=>...]
        foreach ($agg as $row) {
            $inv = (string) ($row->invoice_number ?? '');
            if ($inv === '') continue;
            $invNos[] = $inv;
            $firstByInv[$inv] = [
                'date'   => isset($row->invoice_date) ? (string) $row->invoice_date : null,
                'method' => null,
            ];
        }

        if (empty($invNos)) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'meta'    => ['count' => 0, 'date_from' => $dateFrom, 'date_to' => $dateTo],
            ]);
        }

        // Determine earliest method per invoice (by date asc, then id asc)
        $q2 = DB::table($cols['table'] . ' as pd')
            ->whereIn('pd.' . $cols['number_invoice'], $invNos);

        if (!empty($cols['status'])) {
            $q2->where('pd.' . $cols['status'], 'Paid');
        }

        if ($dateCol) {
            $q2->orderBy('pd.' . $cols['number_invoice'], 'asc')
               ->orderBy('pd.' . $dateCol, 'asc')
               ->orderBy('pd.' . $cols['id'], 'asc');
        } else {
            $q2->orderBy('pd.' . $cols['number_invoice'], 'asc')
               ->orderBy('pd.' . $cols['id'], 'asc');
        }

        $q2->select([
            'pd.' . $cols['number_invoice'] . ' as invoice_number',
            $dateCol ? ('pd.' . $dateCol . ' as d') : DB::raw('NULL as d'),
            !empty($cols['method']) ? ('pd.' . $cols['method'] . ' as method') : DB::raw('NULL as method'),
        ]);

        $pdRows = $q2->get();
        foreach ($pdRows as $r) {
            $key = isset($r->invoice_number) ? (string) $r->invoice_number : '';
            if ($key === '' || !array_key_exists($key, $firstByInv)) continue;
            if ($firstByInv[$key]['method'] === null) {
                $firstByInv[$key]['method'] = isset($r->method) ? (string) $r->method : null;
            }
        }

        // 2) Fetch invoices joined to student for details and optional filters
        $iq = DB::table('tb_mas_invoices as i')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'i.intStudentID')
            ->whereIn('i.invoice_number', $invNos);

        if ($campusId !== null) {
            $iq->where('i.campus_id', $campusId);
        }
        if ($type !== null && $type !== '') {
            $iq->where('i.type', $type);
        }
        if ($invStat !== null && $invStat !== '') {
            $iq->where('i.status', $invStat);
        }

        $invRows = $iq->select(
                'i.*',
                'u.strStudentNumber',
                'u.strFirstname',
                'u.strMiddlename',
                'u.strLastname'
            )
            ->get();

        $rows = [];
        foreach ($invRows as $irow) {
            $invNo = $irow->invoice_number ?? null;
            if ($invNo === null) continue;
            $invKey = (string) $invNo;
            if (!isset($firstByInv[$invKey])) {
                // When filtered out by campus/type/status, skip
                continue;
            }
            $first = $firstByInv[$invKey];

            // Resolve amounts (mirror PDF vatDisabled rule when VAT fields are absent)
            $vatable = isset($irow->invoice_amount) ? (float) $irow->invoice_amount : 0.0;
            $ves     = isset($irow->invoice_amount_ves) ? (float) $irow->invoice_amount_ves : 0.0;
            $vzrs    = isset($irow->invoice_amount_vzrs) ? (float) $irow->invoice_amount_vzrs : 0.0;
            $amountTotal = isset($irow->amount_total) ? (float) $irow->amount_total : 0.0;

            // Determine if ALL VAT/EWT fields are strictly null (do not compute VAT when all are null)
            $isInvAmtNull  = !isset($irow->invoice_amount) || $irow->invoice_amount === null;
            $isVesNull     = !isset($irow->invoice_amount_ves) || $irow->invoice_amount_ves === null;
            $isVzrsNull    = !isset($irow->invoice_amount_vzrs) || $irow->invoice_amount_vzrs === null;
            $isEwtPctNull  = !isset($irow->withholding_tax_percentage) || $irow->withholding_tax_percentage === null;
            $allVatFieldsNull = ($isInvAmtNull && $isVesNull && $isVzrsNull && $isEwtPctNull);

            // Base totals from explicit fields
            $totalSales = round($vatable + $ves + $vzrs, 2);

            if ($allVatFieldsNull) {
                // Do NOT compute VAT if all VAT/EWT fields are null.
                // Set vatable amount to zero explicitly per requirement.
                if ($amountTotal > 0) {
                    $totalSales = round($amountTotal, 2);
                }
                $vatable = 0.0;
                $vat = 0.0;
                $ewtRate = 0.0;
                $ewtAmt = 0.0;
                $netDue = round(($amountTotal > 0 ? $amountTotal : $totalSales), 2);
            } else {
                // VAT and EWT computations (only when not all fields are null)
                $vat = ($vatable !== null && $vatable != 0.0) ? round($vatable * 0.12, 2) : 0.0;
                $ewtRate = isset($irow->withholding_tax_percentage) ? (float) $irow->withholding_tax_percentage : 0.0;
                $ewtAmt  = ($vatable != 0.0 && $ewtRate)
                    ? round($vatable * ($ewtRate / 100.0), 2)
                    : 0.0;

                $netDue = round($totalSales + $vat - $ewtAmt, 2);
            }

            // Payee name
            $nameParts = [];
            if (isset($irow->strLastname) && trim((string)$irow->strLastname) !== '') $nameParts[] = trim((string)$irow->strLastname) . ',';
            if (isset($irow->strFirstname) && trim((string)$irow->strFirstname) !== '') $nameParts[] = trim((string)$irow->strFirstname);
            if (isset($irow->strMiddlename) && trim((string)$irow->strMiddlename) !== '') $nameParts[] = trim((string)$irow->strMiddlename);
            $payeeName = trim(implode(' ', $nameParts));

            // Particulars: remarks or first payload item description
            $particulars = '-';
            try {
                if (isset($irow->remarks) && trim((string)$irow->remarks) !== '') {
                    $particulars = trim((string)$irow->remarks);
                } elseif (isset($irow->payload) && $irow->payload !== null) {
                    $pl = is_array($irow->payload) ? $irow->payload : (json_decode((string)$irow->payload, true) ?: null);
                    if ($pl && isset($pl['items']) && is_array($pl['items']) && !empty($pl['items'])) {
                        $desc = $pl['items'][0]['description'] ?? '';
                        if (is_string($desc) && trim($desc) !== '') {
                            $particulars = trim($desc);
                        }
                    }
                }
            } catch (\Throwable $e) {}

            $method = $first['method'] ?? null;
            $paymentType = ($method !== null && stripos((string)$method, 'cash') !== false) ? 'Cash Sale' : 'Charge Sales';

            $rows[] = [
                'invoice_date'    => $first['date'] ?? null,
                'invoice_number'  => $invNo,
                'student_number'  => isset($irow->strStudentNumber) ? (string) $irow->strStudentNumber : null,
                'payee_name'      => $payeeName !== '' ? $payeeName : null,
                'payment_for'     => isset($irow->type) ? ucwords((string) $irow->type) : null,
                'particulars'     => $particulars,
                'payment_type'    => $paymentType,
                'mop'             => $method,
                'vatable_amount'  => round($vatable, 2),
                'vat_exempt'      => round($ves, 2),
                'zero_rated'      => round($vzrs, 2),
                'total_sales'     => round($totalSales, 2),
                'vat'             => round($vat, 2),
                'ewt_rate'        => is_numeric($ewtRate) ? (float) $ewtRate : null,
                'ewt_amount'      => round($ewtAmt, 2),
                'net_amount_due'  => round($netDue, 2),
            ];
        }

        // Order rows by invoice_date asc, invoice_number asc
        usort($rows, function ($a, $b) {
            $da = $a['invoice_date'] ?? '';
            $db = $b['invoice_date'] ?? '';
            if ($da === $db) {
                return ($a['invoice_number'] <=> $b['invoice_number']);
            }
            return strcmp((string)$da, (string)$db);
        });

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'meta'    => [
                'count'     => count($rows),
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }

    /**
     * GET /api/v1/reports/official-receipts
     * Params:
     *  - date_from: Y-m-d (required)
     *  - date_to:   Y-m-d (required, >= date_from)
     *  - campus_id?: int (optional)
     *
     * Returns a list of Official Receipts (from payment_details with existing OR number)
     * within the inclusive date range. Filters by payment_details.or_date when present;
     * otherwise falls back to the detected date column (paid_at/date/created_at).
     *
     * Output rows:
     *  - or_date, or_number, invoice_number, student_number, payee_name,
     *    payment_for, particulars, payment_received
     */
    public function orReports(Request $request)
    {
        $payload = $request->validate([
            'date_from' => 'required|date_format:Y-m-d',
            'date_to'   => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'campus_id' => 'nullable|integer',
        ]);

        $dateFrom = (string) $payload['date_from'];
        $dateTo   = (string) $payload['date_to'];
        $campusId = array_key_exists('campus_id', $payload) ? (int) $payload['campus_id'] : null;

        /** @var PaymentDetailAdminService $pdSvc */
        $pdSvc = app(PaymentDetailAdminService::class);
        $cols  = $pdSvc->detectColumns();

        // Require payment_details table with an OR number column
        if (!($cols['exists'] ?? false) || empty($cols['number_or'])) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'meta'    => ['count' => 0, 'date_from' => $dateFrom, 'date_to' => $dateTo],
            ]);
        }

        // Prefer explicit 'or_date' when available; else use detected date column
        $dateCol = Schema::hasColumn($cols['table'], 'or_date') ? 'or_date' : ($cols['date'] ?: null);

        // Base query: rows with OR number
        $q = DB::table($cols['table'] . ' as pd')
            ->whereNotNull('pd.' . $cols['number_or']);

        // Status must be Paid when status column exists
        if (!empty($cols['status'])) {
            $q->where('pd.' . $cols['status'], 'Paid');
        }

        // Inclusive date range on chosen date column (when present)
        if ($dateCol) {
            $q->where('pd.' . $dateCol, '>=', $dateFrom)
              ->where('pd.' . $dateCol, '<=', $dateTo);
        }

        // Campus filter: prefer payment_details.student_campus; else join tb_mas_users via student id
        $joinedUsers = false;
        if ($campusId !== null) {
            if (!empty($cols['student_campus'])) {
                $q->where('pd.' . $cols['student_campus'], $campusId);
            } elseif (!empty($cols['student_id'])) {
                $q->leftJoin('tb_mas_users as u', 'u.intID', '=', 'pd.' . $cols['student_id']);
                $q->where('u.campus_id', $campusId);
                $joinedUsers = true;
            }
        }

        // Select normalized columns
        $select = [];
        $select[] = 'pd.' . $cols['id'] . ' as id';
        $select[] = 'pd.' . $cols['number_or'] . ' as or_number';
        $select[] = $dateCol ? ('pd.' . $dateCol . ' as or_date') : DB::raw('NULL as or_date');
        $select[] = !empty($cols['number_invoice']) ? ('pd.' . $cols['number_invoice'] . ' as invoice_number') : DB::raw('NULL as invoice_number');
        $select[] = !empty($cols['student_number']) ? ('pd.' . $cols['student_number'] . ' as student_number') : DB::raw('NULL as student_number');
        $select[] = !empty($cols['student_id']) ? ('pd.' . $cols['student_id'] . ' as student_information_id') : DB::raw('NULL as student_information_id');
        $select[] = !empty($cols['payee_id']) ? ('pd.' . $cols['payee_id'] . ' as payee_id') : DB::raw('NULL as payee_id');
        $select[] = !empty($cols['description']) ? ('pd.' . $cols['description'] . ' as description') : DB::raw('NULL as description');
        $select[] = !empty($cols['remarks']) ? ('pd.' . $cols['remarks'] . ' as remarks') : DB::raw('NULL as remarks');
        $select[] = !empty($cols['subtotal_order']) ? ('pd.' . $cols['subtotal_order'] . ' as subtotal_order') : DB::raw('NULL as subtotal_order');

        // When campusId filtered via join above and student_number not selected, ensure we can still read it if present
        if ($joinedUsers && empty($cols['student_number'])) {
            $select[] = DB::raw('u.strStudentNumber as student_number_joined');
        }

        $q->select($select);

        // Ordering (deterministic)
        if ($dateCol) $q->orderBy('pd.' . $dateCol, 'asc');
        $q->orderBy('pd.' . $cols['number_or'], 'asc');
        $q->orderBy('pd.' . $cols['id'], 'asc');

        $rowsDb = $q->get();

        // Build response rows
        $rows = [];
        foreach ($rowsDb as $r) {
            // Resolve payee name (prefer Payee for non-student payments)
            $payeeName = null;
            try {
                if (isset($r->payee_id) && $r->payee_id) {
                    $p = DB::table('tb_mas_payee')->select('lastname', 'firstname', 'middlename')->where('id', (int)$r->payee_id)->first();
                    if ($p) {
                        $ln = isset($p->lastname) ? trim((string)$p->lastname) : '';
                        $fn = isset($p->firstname) ? trim((string)$p->firstname) : '';
                        $mn = isset($p->middlename) ? trim((string)$p->middlename) : '';
                        $payeeName = trim(($ln !== '' ? $ln . ', ' : '') . $fn . ($mn !== '' ? (' ' . $mn) : ''));
                        if ($payeeName === '' || $payeeName === ',') {
                            $payeeName = null;
                        }
                    }
                } elseif (isset($r->student_information_id) && $r->student_information_id) {
                    $u = DB::table('tb_mas_users')->select('strLastname', 'strFirstname', 'strMiddlename')->where('intID', (int)$r->student_information_id)->first();
                    if ($u) {
                        $ln = isset($u->strLastname) ? trim((string)$u->strLastname) : '';
                        $fn = isset($u->strFirstname) ? trim((string)$u->strFirstname) : '';
                        $mn = isset($u->strMiddlename) ? trim((string)$u->strMiddlename) : '';
                        $payeeName = trim(($ln !== '' ? $ln . ', ' : '') . $fn . ($mn !== '' ? (' ' . $mn) : ''));
                        if ($payeeName === '' || $payeeName === ',') {
                            $payeeName = null;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore name lookup errors
            }

            // Student number fallback when joined via users (no student_number column)
            $studentNumber = isset($r->student_number) ? (string)$r->student_number : null;
            if (!$studentNumber && isset($r->student_number_joined)) {
                $studentNumber = (string)$r->student_number_joined;
            }

            // Payment for / particulars
            $desc = isset($r->description) ? trim((string)$r->description) : '';
            $remarks = isset($r->remarks) ? trim((string)$r->remarks) : '';
            $particulars = $remarks !== '' ? $remarks : ($desc !== '' ? $desc : '-');

            $rows[] = [
                'or_date'         => isset($r->or_date) ? (string)$r->or_date : null,
                'or_number'       => isset($r->or_number) ? $r->or_number : null,
                'invoice_number'  => isset($r->invoice_number) ? $r->invoice_number : null,
                'student_number'  => $studentNumber ?: null,
                'payee_name'      => $payeeName,
                'payment_for'     => $desc !== '' ? $desc : null,
                'particulars'     => $particulars,
                'payment_received'=> isset($r->subtotal_order) ? round((float)$r->subtotal_order, 2) : 0.0,
            ];
        }

        // Order rows by or_date asc, or_number asc
        usort($rows, function ($a, $b) {
            $da = $a['or_date'] ?? '';
            $db = $b['or_date'] ?? '';
            if ($da === $db) {
                return ($a['or_number'] <=> $b['or_number']);
            }
            return strcmp((string)$da, (string)$db);
        });

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'meta'    => [
                'count'     => count($rows),
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }

    /**
     * GET /api/v1/reports/daily-collections
     * Params:
     *  - date_from: Y-m-d (required)
     *  - date_to:   Y-m-d (required, >= date_from)
     *  - campus_id?: int (optional)
     *  - cashier_id?: int (optional, applied only if payment_details.cashier_id exists)
     *
     * Aggregates payment_details rows with status='Paid' (when status column exists) grouped by day.
     * - Prefers payment_details.or_date for date column when available; otherwise uses detected date (paid_at/date/created_at).
     * - Filters by inclusive date range on the chosen date column.
     * - Optional campus filter: prefers payment_details.student_campus; else joins tb_mas_users via student_id.
     * - Optional cashier filter: applied only if payment_details.cashier_id exists.
     *
     * Output JSON:
     * {
     *   "date_from": "Y-m-d",
     *   "date_to": "Y-m-d",
     *   "meta": { "count_rows": int, "grand_total": float },
     *   "daily": [
     *     {
     *       "date": "Y-m-d",
     *       "total_paid": float,
     *       "count_paid": int,
     *       "by_method": { "<method>": float, ... },
     *       "by_cashier": [ { "cashier_id": int, "total": float }, ... ] // only if cashier_id column exists
     *     }
     *   ]
     * }
     */
    public function dailyCollections(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'date_from'  => 'required|date_format:Y-m-d',
            'date_to'    => 'required|date_format:Y-m-d|after_or_equal:date_from',
            'campus_id'  => 'nullable|integer',
            'cashier_id' => 'nullable|integer',
        ]);

        $dateFrom  = (string) $payload['date_from'];
        $dateTo    = (string) $payload['date_to'];
        $campusId  = array_key_exists('campus_id', $payload) ? (int) $payload['campus_id'] : null;
        $cashierId = array_key_exists('cashier_id', $payload) ? (int) $payload['cashier_id'] : null;

        /** @var PaymentDetailAdminService $pdSvc */
        $pdSvc = app(PaymentDetailAdminService::class);
        $cols  = $pdSvc->detectColumns();

        // Require table and subtotal_order to exist
        if (!($cols['exists'] ?? false) || empty($cols['subtotal_order'])) {
            return response()->json([
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'meta'      => ['count_rows' => 0, 'grand_total' => 0.0],
                'daily'     => [],
            ]);
        }

        // Prefer explicit 'or_date' when available; else use detected date column
        $dateCol = Schema::hasColumn($cols['table'], 'or_date') ? 'or_date' : ($cols['date'] ?: null);

        if (!$dateCol) {
            // Without a date column, we cannot build a daily aggregation safely
            return response()->json([
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
                'meta'      => ['count_rows' => 0, 'grand_total' => 0.0],
                'daily'     => [],
            ]);
        }

        // Base query
        $q = DB::table($cols['table'] . ' as pd');

        // Only Paid rows when status column exists
        if (!empty($cols['status'])) {
            $q->where('pd.' . $cols['status'], 'Paid');
        }

        // Inclusive date range
        $q->where('pd.' . $dateCol, '>=', $dateFrom)
          ->where('pd.' . $dateCol, '<=', $dateTo);

        // Campus filter
        $joinedUsers = false;
        if ($campusId !== null) {
            if (!empty($cols['student_campus'])) {
                $q->where('pd.' . $cols['student_campus'], $campusId);
            } elseif (!empty($cols['student_id'])) {
                $q->leftJoin('tb_mas_users as u', 'u.intID', '=', 'pd.' . $cols['student_id']);
                $q->where('u.campus_id', $campusId);
                $joinedUsers = true;
            }
        }

        // Cashier filter (only if column exists)
        $hasCashierCol = Schema::hasColumn($cols['table'], 'cashier_id');
        if ($cashierId !== null && $hasCashierCol) {
            $q->where('pd.cashier_id', $cashierId);
        }

        // Select minimal fields for aggregation
        $select = [];
        $select[] = DB::raw('DATE(pd.' . $dateCol . ') as d');
        $select[] = 'pd.' . $cols['subtotal_order'] . ' as subtotal_order';
        $select[] = !empty($cols['method']) ? ('pd.' . $cols['method'] . ' as method') : DB::raw('NULL as method');
        $select[] = $hasCashierCol ? DB::raw('pd.cashier_id as cashier_id') : DB::raw('NULL as cashier_id');

        $rowsDb = $q->select($select)->get();

        // Initialize buckets for each day in range
        $daily = [];
        try {
            $period = new \DatePeriod(
                new \DateTime($dateFrom),
                new \DateInterval('P1D'),
                (new \DateTime($dateTo))->modify('+1 day')
            );
            foreach ($period as $dt) {
                $d = $dt->format('Y-m-d');
                $daily[$d] = [
                    'date'       => $d,
                    'total_paid' => 0.0,
                    'count_paid' => 0,
                    'by_method'  => [],
                    // store map internally for cashiers; convert to array later
                    '_by_cashier_map' => [],
                ];
            }
        } catch (\Throwable $e) {
            // Fallback: no pre-initialization if DatePeriod fails (shouldn't happen with valid Y-m-d)
        }

        $grandTotal = 0.0;
        $countRows  = 0;

        foreach ($rowsDb as $r) {
            $countRows += 1;
            $d = isset($r->d) ? (string) $r->d : null;
            if ($d === null || $d === '') continue;

            if (!isset($daily[$d])) {
                // In case no pre-initialization (or date outside), create on the fly
                $daily[$d] = [
                    'date'       => $d,
                    'total_paid' => 0.0,
                    'count_paid' => 0,
                    'by_method'  => [],
                    '_by_cashier_map' => [],
                ];
            }

            $amt = isset($r->subtotal_order) ? (float) $r->subtotal_order : 0.0;
            $daily[$d]['total_paid'] += $amt;
            $daily[$d]['count_paid'] += 1;
            $grandTotal += $amt;

            // By method
            $m = isset($r->method) && trim((string)$r->method) !== '' ? (string) $r->method : 'Unknown';
            if (!isset($daily[$d]['by_method'][$m])) {
                $daily[$d]['by_method'][$m] = 0.0;
            }
            $daily[$d]['by_method'][$m] += $amt;

            // By cashier (only if column exists and value present)
            if ($hasCashierCol) {
                $cid = isset($r->cashier_id) ? $r->cashier_id : null;
                if ($cid !== null && $cid !== '') {
                    $cidKey = (int) $cid;
                    if (!isset($daily[$d]['_by_cashier_map'][$cidKey])) {
                        $daily[$d]['_by_cashier_map'][$cidKey] = 0.0;
                    }
                    $daily[$d]['_by_cashier_map'][$cidKey] += $amt;
                }
            }
        }

        // Normalize daily array and convert by_cashier map to array
        ksort($daily);
        $dailyOut = [];
        foreach ($daily as $d => $bucket) {
            // Convert by_cashier map to array if we had cashier column
            $byCashierArr = [];
            if ($hasCashierCol && !empty($bucket['_by_cashier_map']) && is_array($bucket['_by_cashier_map'])) {
                ksort($bucket['_by_cashier_map']);
                foreach ($bucket['_by_cashier_map'] as $cid => $total) {
                    $byCashierArr[] = [
                        'cashier_id' => (int) $cid,
                        'total'      => round((float) $total, 2),
                    ];
                }
            }

            // Round method totals
            $byMethodOut = [];
            foreach ($bucket['by_method'] as $method => $val) {
                $byMethodOut[$method] = round((float) $val, 2);
            }

            $dailyOut[] = [
                'date'       => $bucket['date'],
                'total_paid' => round((float) $bucket['total_paid'], 2),
                'count_paid' => (int) $bucket['count_paid'],
                'by_method'  => $byMethodOut,
                'by_cashier' => $byCashierArr,
            ];
        }

        return response()->json([
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'meta'      => [
                'count_rows' => (int) $countRows,
                'grand_total'=> round((float) $grandTotal, 2),
            ],
            'daily'     => $dailyOut,
        ]);
    }
}
