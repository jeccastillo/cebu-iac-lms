<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InvoiceGenerateRequest;
use App\Services\InvoiceService;
use App\Models\Cashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Pdf\InvoicePdf;
use App\Http\Requests\Api\V1\InvoiceUpdateRequest;
use App\Services\SystemLogService;
use App\Services\PaymentDetailAdminService;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $svc)
    {
        // Routes are protected via role middleware (finance,admin) in api.php
    }

    /**
     * GET /api/v1/finance/invoices
     * Filters:
     *  - student_id?: int
     *  - student_number?: string
     *  - syid?: int
     *  - type?: string
     *  - status?: string
     *  - campus_id?: int
     */
    public function index(Request $request)
    {
        // Backward-compat: if registration_id is provided, resolve missing student_id/syid
        $studentId      = $request->input('student_id');
        $syid           = $request->input('syid');
        $registrationId = $request->input('registration_id');
        $term           = $request->input('term');

        // Accept 'term' as alias for 'syid' when not explicitly provided
        if ((empty($syid) || $syid === '') && $term !== null && $term !== '' && is_numeric($term)) {
            $syid = (int) $term;
        }

        if (!empty($registrationId) && (empty($studentId) || empty($syid))) {
            try {
                $reg = DB::table('tb_mas_registration')
                    ->where('intRegistrationID', (int) $registrationId)
                    ->first();
                if ($reg) {
                    if (empty($studentId) && isset($reg->intStudentID)) {
                        $studentId = (int) $reg->intStudentID;
                    }
                    if (empty($syid)) {
                        if (isset($reg->syid)) {
                            $syid = (int) $reg->syid;
                        } elseif (isset($reg->intAYID)) {
                            // Fallback: registration table may use intAYID for term id
                            $syid = (int) $reg->intAYID;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore db errors and fall back to explicit inputs
            }
        }

        $filters = [
            'student_id'      => $studentId,
            'student_number'  => $request->input('student_number'),
            'syid'            => $syid,
            'type'            => $request->input('type'),
            'status'          => $request->input('status'),
            'campus_id'       => $request->input('campus_id'),
        ];

        $items = $this->svc->list($filters);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => ['count' => count($items)],
        ]);
    }

    /**
     * GET /api/v1/finance/invoices/{id}
     */
    public function show($id)
    {
        $row = $this->svc->get((int) $id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => "Invoice id {$id} not found",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * GET /api/v1/finance/invoices/{id}/pdf
     * Streams a minimal PDF of the invoice (inline) using FPDI (Letter, Helvetica).
     */
    public function pdf($id, Request $request)
    {
        $row = $this->svc->get((int) $id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => "Invoice id {$id} not found",
            ], 404);
        }

        // Normalize shape for processing
        $invoice = is_array($row) ? $row : (array) $row;

        // Resolve items (normalize each into description/qty/price/amount)
        $rawItems = [];
        if (isset($invoice['items']) && is_array($invoice['items'])) {
            $rawItems = $invoice['items'];
        } elseif (isset($invoice['invoice_items']) && is_array($invoice['invoice_items'])) {
            $rawItems = $invoice['invoice_items'];
        }

        // Resolve display fields with fallbacks
        $invNo   = $invoice['invoice_number'] ?? ($invoice['number'] ?? null);
        $posted  = $invoice['posted_at'] ?? ($invoice['created_at'] ?? null);
        $type    = $invoice['type'] ?? '-';
        // Total: first numeric among amount_total, amount, total
        $total = null;
        foreach (['amount_total', 'amount', 'total'] as $k) {
            if (isset($invoice[$k]) && is_numeric($invoice[$k])) { $total = (float) $invoice[$k]; break; }
        }
        if ($total === null) $total = 0.0;

        // Student name
        $studentName = '';
        try {
            if (!empty($invoice['student_id'])) {
                $u = DB::table('tb_mas_users')->where('intID', (int) $invoice['student_id'])->first();
                if ($u) {
                    $last = isset($u->strLastname) ? trim((string) $u->strLastname) : '';
                    $first = isset($u->strFirstname) ? trim((string) $u->strFirstname) : '';
                    $middle = isset($u->strMiddlename) ? trim((string) $u->strMiddlename) : '';
                    $studentName = ($last !== '' ? ($last . ', ') : '') . $first . ($middle !== '' ? (' ' . $middle) : '');
                    $studentNumber = isset($u->strStudentNumber) ? trim((string) $u->strStudentNumber) : '';
                }
            }
        } catch (\Throwable $e) {}

        // Term label (when syid available)
        $termLabel = '';
        $termForShs = '';
        $termForUg  = '';
        try {
            if (!empty($invoice['syid'])) {
                $t = DB::table('tb_mas_sy')->where('intID', (int) $invoice['syid'])->first();
                if ($t) {
                    $ySpan   = (string) $t->strYearStart . '-' . (string) $t->strYearEnd;
                    $enumSem = trim((string) ($t->enumSem ?? ''));     // e.g., "1st Sem"
                    $termLbl = trim((string) ($t->term_label ?? ''));  // e.g., "1st Term"

                    // Existing combined label (kept for backward-compat or other uses)
                    $termLabel = trim($enumSem . ' ' . $termLbl . ' ' . $ySpan);

                    // Separate display variants used for reservation printouts
                    // SHS prefers Sem-based label; UG prefers Term-based label; fall back to whichever is present
                    $termForShs = trim((($enumSem !== '' ? $enumSem : $termLbl)) . ' ' . $ySpan);
                    $termForUg  = trim((($termLbl !== '' ? $termLbl : $enumSem)) . ' ' . $ySpan);
                }
            }
        } catch (\Throwable $e) {}

        // Normalize items for renderer
        $items = [];
        foreach ($rawItems as $it) {
            if (!is_array($it)) continue;
            $desc = isset($it['description']) ? (string) $it['description'] : ((isset($it['name']) ? (string)$it['name'] : ''));
            $amt  = isset($it['amount']) ? (float) $it['amount'] : (isset($it['price']) ? (float)$it['price'] : 0.0);
            if ($desc === '' && $amt == 0.0) continue;
            $items[] = [
                'description' => $desc !== '' ? $desc : 'Item',
                'qty' => 1,
                'price' => $amt,
                'amount' => $amt,
            ];
        }

        // Synthesize single line when items are absent but we have a total
        $reservationSignature = false;
        if (empty($items)) {
            // Default description
            $desc = ($invoice['type'])?strtoupper($invoice['type']):'Invoice amount';

            // Attempt to detect if this invoice corresponds to a Reservation Payment
            $isReservation = false;
            $appScope = 'ug'; // 'shs' or 'ug'
            try {
                // Resolve applicant type scope (latest applicant_data for this student, optionally filtered by syid)
                if (!empty($invoice['student_id'])) {
                    $adQ = DB::table('tb_mas_applicant_data as ad')
                        ->where('ad.user_id', (int) $invoice['student_id'])
                        ->orderBy('ad.id', 'desc');
                    if (\Illuminate\Support\Facades\Schema::hasColumn('tb_mas_applicant_data', 'syid') && !empty($invoice['syid'])) {
                        $adQ->where('ad.syid', (int) $invoice['syid']);
                    }
                    $ad = $adQ->first();
                    if ($ad && \Illuminate\Support\Facades\Schema::hasColumn('tb_mas_applicant_data', 'applicant_type') && !empty($ad->applicant_type)) {
                        $trow = DB::table('tb_mas_applicant_types')->where('intID', (int) $ad->applicant_type)->select('type')->first();
                        if ($trow && strtolower((string) $trow->type) === 'shs') {
                            $appScope = 'shs';
                        }
                    }
                }

                // DB-based detection of reservation payments for this student/term
                $colsRes = app(PaymentDetailAdminService::class)->detectColumns();
                if (
                    ($colsRes['exists'] ?? false) &&
                    !empty($colsRes['table']) &&
                    !empty($colsRes['student_id']) &&
                    !empty($invoice['student_id'])
                ) {
                    $qRes = DB::table($colsRes['table'])
                        ->where($colsRes['student_id'], (int) $invoice['student_id'])
                        ->where($colsRes['description'], 'like', 'Reservation%');
                    if (!empty($colsRes['sy_reference']) && !empty($invoice['syid'])) {
                        $qRes->where($colsRes['sy_reference'], (int) $invoice['syid']);
                    }
                    if (!empty($colsRes['status'])) {
                        $qRes->where($colsRes['status'], 'Paid');
                    }
                    $isReservation = $qRes->exists();
                }
            } catch (\Throwable $e) {
                // fail open; keep defaults
            }

            // Build description depending on type/reservation context
            // Restrict reservation printout to non-tuition invoices only (tuition invoices should not show reservation header/notes/signature)
            if ($isReservation && strtolower((string) $type) == 'reservation payment') {
                $termDisplay = $appScope === 'shs'
                    ? ($termForShs !== '' ? $termForShs : $termLabel)
                    : ($termForUg !== '' ? $termForUg : $termLabel);
                $prefix = $appScope === 'shs' ? 'SHS' : 'UG';
                $desc = $prefix . ' Reservation Payment' . ($termDisplay !== '' ? (' / ' . $termDisplay) : '');

                // Main amount line
                $items[] = [
                    'description' => $desc,
                    'qty' => 1,
                    'price' => (float) $total,
                    'amount' => (float) $total,
                ];

                // Notes below the main line (no qty/price/amount shown)
                $note1 = $appScope === 'shs'
                    ? ('RESERVATION FEE FOR SENIOR HIGHSCHOOL' . ($termDisplay !== '' ? (' for ' . $termDisplay) : ''))
                    : ('RESERVATION FEE, UNDERGRAD' . ($termDisplay !== '' ? (' for ' . $termDisplay) : ''));
                $items[] = ['description' => $note1, 'note_only' => true];
                $items[] = ['description' => '"NON REFUNDABLE", "NON TRANSFERABLE"', 'note_only' => true];
                // Enable signature line rendering for reservation invoices
                $reservationSignature = true;
            } else {
                if (strtolower((string)$type) === 'tuition' && $termLabel !== '') {
                    $desc = 'UG Tuition Fee / ' . $termLabel;
                } elseif (strtolower((string)$type) === 'tuition') {
                    $desc = 'UG Tuition Fee';
                }
                $items[] = [
                    'description' => $desc,
                    'qty' => 1,
                    'price' => (float) $total,
                    'amount' => (float) $total,
                ];
            }
        }

        // Format date as m/d/Y
        $dateStr = '';
        try {
            if (!empty($posted)) {
                $ts = strtotime((string) $posted);
                if ($ts !== false) {
                    $dateStr = date('m/d/Y', $ts);
                }
            }
        } catch (\Throwable $e) {}

        // Before building the DTO, inject Reservation Payment offset as a negative line item (if any)
        try {
            $reservationSum = 0.0;

            // Apply reservation offset only for Tuition invoices
            if (strtolower((string) $type) === 'tuition') {
                // Use PaymentDetailAdminService column detection to be schema-safe
                $cols = app(PaymentDetailAdminService::class)->detectColumns();
                if (
                    ($cols['exists'] ?? false) &&
                    !empty($cols['table']) &&
                    !empty($cols['student_id']) &&
                    !empty($cols['sy_reference']) &&
                    !empty($cols['description']) &&
                    !empty($cols['subtotal_order'])
                ) {
                    // Identifier is term id (syid) + Reservation description (not invoice number)
                    $q = DB::table($cols['table'])
                        ->where($cols['student_id'], (int) ($invoice['student_id'] ?? 0))
                        ->where($cols['sy_reference'], (int) ($invoice['syid'] ?? 0))
                        ->where($cols['description'], 'like', 'Reservation%');

                    // Filter to Paid status when status column exists
                    if (!empty($cols['status'])) {
                        $q->where($cols['status'], 'Paid');
                    }

                    $reservationSum = (float) $q->sum($cols['subtotal_order']);
                }

                if ($reservationSum > 0) {
                    $neg = -1 * round($reservationSum, 2);
                    $items[] = [
                        'description' => 'Reservation Payment',
                        'qty'         => 1,
                        'price'       => $neg,
                        'amount'      => $neg,
                    ];
                    $total = (float) $total - (float) $reservationSum;
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore any DB/schema errors to avoid breaking PDF rendering
        }

        // Compute first Tuition payment amount for this invoice (to display in the PDF anchors)
        $firstTuitionPaid = null;
        try {
            $cols2 = app(PaymentDetailAdminService::class)->detectColumns();
            if (
                ($cols2['exists'] ?? false) &&
                !empty($cols2['table']) &&
                !empty($cols2['description']) &&
                !empty($cols2['subtotal_order']) &&
                !empty($cols2['student_id']) &&
                !empty($cols2['sy_reference']) &&
                !empty($invoice['student_id']) &&
                !empty($invoice['syid'])
            ) {
                // Strictly identify by student_id + syid (do not use invoice_number)
                $q2 = DB::table($cols2['table'])
                    ->where($cols2['description'], 'like', 'Tuition%')
                    ->where($cols2['student_id'], (int) $invoice['student_id'])
                    ->where($cols2['sy_reference'], (int) $invoice['syid']);

                // Restrict to 'Paid' rows when status column exists
                if (!empty($cols2['status'])) {
                    $q2->where($cols2['status'], 'Paid');
                }

                // Oldest (first) payment by date when available; else by id
                if (!empty($cols2['date'])) {
                    $q2->orderBy($cols2['date'], 'asc');
                }
                $q2->orderBy('id', 'asc');

                $r2 = $q2->first();
                if ($r2) {
                    $firstTuitionPaid = round((float) ($r2->{$cols2['subtotal_order']} ?? 0), 2);
                }
            }
        } catch (\Throwable $e) {
            // Ignore schema issues
        }

        // Resolve footer cashier name (bottom-right)
        // Priority: invoice.cashier_id -> X-Faculty-ID header -> created_by
        $footerName = null;
        try {
            $footerFacultyId = null;

            // 1) From invoice.cashier_id -> tb_mas_cashiers.faculty_id
            if (!empty($invoice['cashier_id'])) {
                $cRow = DB::table('tb_mas_cashiers')
                    ->where('intID', (int) $invoice['cashier_id'])
                    ->select('faculty_id')
                    ->first();
                if ($cRow && !empty($cRow->faculty_id)) {
                    $footerFacultyId = (int) $cRow->faculty_id;
                }
            }

            // 2) From header X-Faculty-ID
            if (!$footerFacultyId) {
                $hdrFaculty = $request->header('X-Faculty-ID');
                if ($hdrFaculty !== null && $hdrFaculty !== '' && is_numeric($hdrFaculty)) {
                    $footerFacultyId = (int) $hdrFaculty;
                }
            }

            // 3) From invoice.created_by
            if (!$footerFacultyId && !empty($invoice['created_by'])) {
                $footerFacultyId = (int) $invoice['created_by'];
            }

            // Lookup faculty name
            if ($footerFacultyId) {
                $f = DB::table('tb_mas_faculty')
                    ->where('intID', $footerFacultyId)
                    ->select('strFirstname', 'strMiddlename', 'strLastname')
                    ->first();
                if ($f) {
                    $parts = [];
                    if (!empty($f->strFirstname))  $parts[] = trim((string) $f->strFirstname);
                    if (!empty($f->strMiddlename)) $parts[] = trim((string) $f->strMiddlename);
                    if (!empty($f->strLastname))   $parts[] = trim((string) $f->strLastname);
                    $footerName = trim(implode(' ', array_filter($parts, function ($x) { return $x !== null && $x !== ''; })));
                }
            }
        } catch (\Throwable $e) {
            // Swallow any DB/lookup errors to avoid breaking PDF generation
        }

        // Build DTO for renderer
        $dto = [
            'number'       => $invNo,
            'date'         => $dateStr,
            'student_name' => $studentName,
            'student_number'=>$studentNumber,
            'term_label'   => $termLabel,
            'items'        => $items,
            'total'        => (float) $total,
            'footer_name'  => $footerName,
            'amount_paid_first_tuition' => $firstTuitionPaid,
            'reservation_signature' => isset($reservationSignature) ? (bool)$reservationSignature : false,
        ];

        // Render and stream inline
        $renderer = app(InvoicePdf::class);
        $content = $renderer->render($dto);

        $filename = 'invoice-' . (($invNo !== null && $invNo !== '') ? $invNo : $id) . '.pdf';
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * POST /api/v1/finance/invoices/generate
     * Body: validated by InvoiceGenerateRequest
     */
    public function generate(InvoiceGenerateRequest $request)
    {
        $v = $request->validated();

        $type      = (string) $v['type'];
        $studentId = (int) $v['student_id'];
        $syid      = (int) $v['syid'];

        // Build options for service
        $options = [];
        if (array_key_exists('items', $v))          $options['items']          = $v['items'];
        if (array_key_exists('amount', $v))         $options['amount']         = $v['amount'];
        if (array_key_exists('status', $v))         $options['status']         = $v['status'];
        if (array_key_exists('posted_at', $v))      $options['posted_at']      = $v['posted_at'];
        if (array_key_exists('due_at', $v))         $options['due_at']         = $v['due_at'];
        if (array_key_exists('remarks', $v))        $options['remarks']        = $v['remarks'];
        if (array_key_exists('campus_id', $v))      $options['campus_id']      = $v['campus_id'];
        if (array_key_exists('cashier_id', $v))     $options['cashier_id']     = $v['cashier_id'];
        if (array_key_exists('registration_id', $v))$options['registration_id']= $v['registration_id'];
        if (array_key_exists('invoice_number', $v)) $options['invoice_number'] = $v['invoice_number'];

        // Default campus_id if not provided: use global selected campus precedence
        if (!array_key_exists('campus_id', $options) || $options['campus_id'] === null || $options['campus_id'] === '') {
            // 1) request input (query/body)
            $reqCampus = $request->input('campus_id');
            if ($reqCampus !== null && $reqCampus !== '' && is_numeric($reqCampus)) {
                $options['campus_id'] = (int) $reqCampus;
            } else {
                // 2) header X-Campus-ID
                $hdrCampus = $request->header('X-Campus-ID');
                if ($hdrCampus !== null && $hdrCampus !== '' && is_numeric($hdrCampus)) {
                    $options['campus_id'] = (int) $hdrCampus;
                } else {
                    // 3) infer from acting cashier by X-Faculty-ID
                    $hdrFaculty = $request->header('X-Faculty-ID');
                    if ($hdrFaculty !== null && $hdrFaculty !== '' && is_numeric($hdrFaculty)) {
                        $cashier = Cashier::query()->where('faculty_id', (int) $hdrFaculty)->first();
                        if ($cashier && isset($cashier->campus_id)) {
                            $options['campus_id'] = (int) $cashier->campus_id;
                        }
                    }
                }
            }
        }

        // If invoice_number not provided, try assigning from acting cashier's invoice_current and set cashier_id when resolvable.
        $usedInvoiceNumber = null;
        $actingCashier = null;
        if (!array_key_exists('invoice_number', $options) || $options['invoice_number'] === null || $options['invoice_number'] === '') {
            // Prefer explicit cashier_id when provided
            if (array_key_exists('cashier_id', $options) && !empty($options['cashier_id'])) {
                $actingCashier = Cashier::query()->where('intID', (int) $options['cashier_id'])->first();
            }
            // Fallback: resolve by X-Faculty-ID header
            if (!$actingCashier) {
                $hdrFaculty = $request->header('X-Faculty-ID');
                if ($hdrFaculty !== null && $hdrFaculty !== '' && is_numeric($hdrFaculty)) {
                    $actingCashier = Cashier::query()->where('faculty_id', (int) $hdrFaculty)->first();
                    if ($actingCashier && (!array_key_exists('cashier_id', $options) || empty($options['cashier_id']))) {
                        $options['cashier_id'] = (int) $actingCashier->intID;
                    }
                }
            }
            // Assign number from cashier pointer when available
            if ($actingCashier && !empty($actingCashier->invoice_current)) {
                $options['invoice_number'] = (int) $actingCashier->invoice_current;
                $usedInvoiceNumber = (int) $actingCashier->invoice_current;
            }
        }

        // Failsafe: require cashier and valid current invoice when invoice_number not explicitly provided
        if (!array_key_exists('invoice_number', $options) || $options['invoice_number'] === null || $options['invoice_number'] === '') {
            if (!$actingCashier) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NO_CASHIER',
                    'message' => 'Cashier account is required to generate an invoice without an explicit invoice_number.',
                ], 422);
            }
            if (empty($actingCashier->invoice_current)) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NO_CASHIER_INVOICE_CURRENT',
                    'message' => 'Cashier current invoice is not set.',
                ], 422);
            }
        }

        $actorId = null;
        try {
            $actor = $request->user();
            if ($actor && isset($actor->id)) {
                $actorId = (int) $actor->id;
            }
        } catch (\Throwable $e) {
            // ignore when auth not available
        }

        $row = $this->svc->generate($type, $studentId, $syid, $options, $actorId);

        // System log: create invoice
        SystemLogService::log('create', 'Invoice', (int) ($row['id'] ?? 0), null, $row, $request);

        // Increment cashier invoice_current if we consumed an invoice number from a resolved cashier
        if ($usedInvoiceNumber !== null && $actingCashier && isset($actingCashier->intID)) {
            try {
                Cashier::query()
                    ->where('intID', (int) $actingCashier->intID)
                    ->update(['invoice_current' => (int) $actingCashier->invoice_current + 1]);
            } catch (\Throwable $e) {
                // Do not block response on pointer increment failure
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $row,
        ], 201);
    }

    /**
     * POST /api/v1/finance/invoices (admin-only)
     * Alias to generate() for admin workflows, allowing explicit invoice_number or standard generation.
     */
    public function store(InvoiceGenerateRequest $request)
    {
        return $this->generate($request);
    }

    /**
     * PUT /api/v1/finance/invoices/{id} (admin-only)
     * Partial update for invoice fields.
     */
    public function update($id, InvoiceUpdateRequest $request)
    {
        $id = (int) $id;

        $existing = DB::table('tb_mas_invoices')->where('intID', $id)->first();
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => "Invoice id {$id} not found",
            ], 404);
        }

        $v = $request->validated();

        // Capture old state for system log
        $old = $this->svc->get($id);

        // Enforce unique invoice_number if provided
        // if (array_key_exists('invoice_number', $v) && $v['invoice_number'] !== null && $v['invoice_number'] !== '') {
        //     $dup = DB::table('tb_mas_invoices')
        //         ->where('invoice_number', (int) $v['invoice_number'])
        //         ->where('intID', '!=', $id)
        //         ->exists();
        //     if ($dup) {
        //         return response()->json([
        //             'success' => false,
        //             'code'    => 'DUPLICATE_INVOICE_NUMBER',
        //             'message' => 'Invoice number already exists.',
        //         ], 422);
        //     }
        // }

        $update = [];
        foreach (['status','posted_at','due_at','remarks','campus_id','cashier_id'] as $f) {
            if (array_key_exists($f, $v)) {
                $update[$f] = $v[$f];
            }
        }
        if (array_key_exists('invoice_number', $v)) {
            $update['invoice_number'] = $v['invoice_number'];
        }
        if (array_key_exists('amount', $v) && is_numeric($v['amount'])) {
            $update['amount_total'] = round((float)$v['amount'], 2);
        }
        if (array_key_exists('payload', $v) && is_array($v['payload'])) {
            // DB builder update: ensure JSON encoding
            $update['payload'] = json_encode($v['payload']);
        }

        // Track updater when available
        try {
            $actor = $request->user();
            if ($actor && isset($actor->id)) {
                $update['updated_by'] = (int) $actor->id;
            }
        } catch (\Throwable $e) {}

        $update['updated_at'] = now()->toDateTimeString();

        DB::table('tb_mas_invoices')->where('intID', $id)->update($update);

        $row = $this->svc->get($id);

        // System log: update invoice
        SystemLogService::log('update', 'Invoice', (int) $id, $old, $row, $request);

        return response()->json([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * DELETE /api/v1/finance/invoices/{id} (admin-only)
     * Hard delete invoice.
     */
    public function destroy($id, Request $request)
    {
        $id = (int) $id;

        $existing = DB::table('tb_mas_invoices')->where('intID', $id)->first();
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => "Invoice id {$id} not found",
            ], 404);
        }

        $existingNormalized = $this->svc->get($id);

        DB::table('tb_mas_invoices')->where('intID', $id)->delete();

        // System log: delete invoice
        SystemLogService::log('delete', 'Invoice', (int) $id, $existingNormalized, null, $request);

        return response()->json([
            'success' => true,
            'message' => "Invoice id {$id} deleted",
        ]);
    }
}
