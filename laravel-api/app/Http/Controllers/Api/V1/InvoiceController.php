<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InvoiceGenerateRequest;
use App\Services\InvoiceService;
use App\Models\Cashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Http\Requests\Api\V1\InvoiceUpdateRequest;
use App\Services\SystemLogService;

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
     * Streams a minimal PDF of the invoice (inline).
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

        // Normalize shape for the view
        $invoice = is_array($row) ? $row : (array) $row;
        $items = [];
        if (isset($invoice['items']) && is_array($invoice['items'])) {
            $items = $invoice['items'];
        } elseif (isset($invoice['invoice_items']) && is_array($invoice['invoice_items'])) {
            $items = $invoice['invoice_items'];
        }

        // Resolve display fields with fallbacks
        $invNo = $invoice['invoice_number'] ?? ($invoice['number'] ?? null);
        $posted = $invoice['posted_at'] ?? ($invoice['created_at'] ?? null);
        $type = $invoice['type'] ?? '-';
        $status = $invoice['status'] ?? '-';
        $remarks = $invoice['remarks'] ?? '';
        // Total: first numeric among amount_total, amount, total
        $total = null;
        foreach (['amount_total', 'amount', 'total'] as $k) {
            if (isset($invoice[$k]) && is_numeric($invoice[$k])) { $total = (float) $invoice[$k]; break; }
        }
        if ($total === null) $total = 0;

        $pdf = PDF::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'items'   => $items,
            'meta'    => [
                'number'   => $invNo,
                'posted'   => $posted,
                'type'     => $type,
                'status'   => $status,
                'remarks'  => $remarks,
                'total'    => $total,
            ],
        ])
        ->setPaper('a4')
        ->setOption('margin-top', '12mm')
        ->setOption('margin-right', '12mm')
        ->setOption('margin-bottom', '12mm')
        ->setOption('margin-left', '12mm');

        $filename = 'invoice-' . ($invNo ?: $id) . '.pdf';
        return $pdf->inline($filename);
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
