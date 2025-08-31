<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InvoiceGenerateRequest;
use App\Services\InvoiceService;
use App\Models\Cashier;
use Illuminate\Http\Request;

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
        $filters = [
            'student_id'      => $request->input('student_id'),
            'student_number'  => $request->input('student_number'),
            'syid'            => $request->input('syid'),
            'type'            => $request->input('type'),
            'status'          => $request->input('status'),
            'campus_id'       => $request->input('campus_id'),
            'registration_id' => $request->input('registration_id'),
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
}
