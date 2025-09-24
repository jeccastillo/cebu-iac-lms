<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FinanceService;
use App\Services\StudentLedgerService;
use App\Services\Pdf\OfficialReceiptPdf;
use App\Services\PaymentDetailAdminService;

class FinanceController extends Controller
{
    protected FinanceService $finance;

    public function __construct(FinanceService $finance)
    {
        $this->finance = $finance;
    }

    /**
     * GET /api/v1/finance/or/{or}/pdf
     * Streams an Official Receipt PDF inline.
     */
    public function orPdf($or, Request $request)
    {
        $orStr = (string) $or;

        // Defaults
        $companyName = 'iACADEMY, Inc.';
        $companyTin  = 'VAT REG TIN: 214-749-003-00003';
        $companyLines = [];

        // Resolve campus/company address (best-effort; mimic invoice header behavior)
        try {
            $campusId = null;
            // Prefer explicit campus header or query
            $hdrCampus = $request->header('X-Campus-ID');
            $qpCampus  = $request->query('campus_id');
            if ($hdrCampus !== null && $hdrCampus !== '' && is_numeric($hdrCampus)) {
                $campusId = (int) $hdrCampus;
            } elseif ($qpCampus !== null && $qpCampus !== '' && is_numeric($qpCampus)) {
                $campusId = (int) $qpCampus;
            }
            // If still not provided, try student's campus_id (when resolvable)
            if (!$campusId && $studentId) {
                try {
                    $u = DB::table('tb_mas_users')->where('intID', (int)$studentId)->select('campus_id')->first();
                    if ($u && isset($u->campus_id) && is_numeric($u->campus_id)) {
                        $campusId = (int)$u->campus_id;
                    }
                } catch (\Throwable $e) {}
            }

            // Helper to push campus address/description when available
            $pushCampus = function ($camp) use (&$companyLines) {
                if (!$camp) return;
                $addr = trim((string) ($camp->address ?? ''));
                if ($addr !== '') {
                    $companyLines[] = $addr;
                } elseif (!empty($camp->description)) {
                    $companyLines[] = trim((string) $camp->description);
                }
            };

            if ($campusId) {
                $camp = DB::table('tb_mas_campuses')
                    ->where('intID', $campusId)
                    ->select('address', 'description')
                    ->first();
                $pushCampus($camp);
            }

            // Fallback: first campus (active preferred)
            if (empty($companyLines)) {
                $camp = DB::table('tb_mas_campuses')
                    ->orderBy('status', 'desc')
                    ->orderBy('intID', 'asc')
                    ->select('address', 'description')
                    ->first();
                $pushCampus($camp);
            }

            // Final fallback: use default Cebu campus lines from spec if still empty
            if (empty($companyLines)) {
                $companyLines = [
                    'Floor Filinvest Cebu Cyberzone Tower Two, Salinas Drive cor. W. Geonzon St.,',
                    'Cebu IT Park, Apas Cebu City 6000, Cebu Philippines',
                ];
            }
        } catch (\Throwable $e) {
            // ignore campus lookup errors; use default fallback if empty
            if (empty($companyLines)) {
                $companyLines = [
                    'Floor Filinvest Cebu Cyberzone Tower Two, Salinas Drive cor. W. Geonzon St.,',
                    'Cebu IT Park, Apas Cebu City 6000, Cebu Philippines',
                ];
            }
        }

        // Find a payment_details row by OR number (normalized columns)
        $pd = null;
        $studentId = null;
        $payeeId = null;
        $studentNumber = null;
        $method = null;
        $postedAt = null;
        $invoiceRefNo = null;
        $pdDesc = null;
        $pdAmount = null;

        try {
            /** @var \App\Services\PaymentDetailAdminService $pdSvc */
            $pdSvc = app(PaymentDetailAdminService::class);
            $cols = $pdSvc->detectColumns();
            if ($cols['exists'] && $cols['number_or']) {
                $select = ['id'];
                if ($cols['student_id']) $select[] = $cols['student_id'] . ' as student_information_id';
                if ($cols['payee_id'])   $select[] = $cols['payee_id'] . ' as payee_id';
                if ($cols['student_number']) $select[] = $cols['student_number'] . ' as student_number';
                if ($cols['description']) $select[] = $cols['description'] . ' as description';
                if ($cols['subtotal_order']) $select[] = $cols['subtotal_order'] . ' as subtotal_order';
                if ($cols['number_invoice']) $select[] = $cols['number_invoice'] . ' as invoice_number';
                if ($cols['remarks']) $select[] = $cols['remarks'] . ' as remarks';
                if ($cols['method']) $select[] = $cols['method'] . ' as method';
                if ($cols['date']) $select[] = $cols['date'] . ' as posted_at';

                $pd = DB::table($cols['table'])
                    ->where($cols['number_or'], $orStr)
                    ->orderBy('id', 'desc')
                    ->select($select)
                    ->first();

                if ($pd) {
                    $studentId     = isset($pd->student_information_id) ? (int) $pd->student_information_id : null;
                    $payeeId       = isset($pd->payee_id) ? (int) $pd->payee_id : null;
                    $studentNumber = isset($pd->student_number) ? (string) $pd->student_number : null;
                    $method        = isset($pd->method) ? (string) $pd->method : null;
                    $postedAt      = isset($pd->posted_at) ? (string) $pd->posted_at : null;
                    $invoiceRefNo  = isset($pd->invoice_number) ? (string) $pd->invoice_number : null;
                    $pdDesc        = isset($pd->description) ? (string) $pd->description : null;
                    $pdAmount      = isset($pd->subtotal_order) ? (float) $pd->subtotal_order : null;
                }
            }
        } catch (\Throwable $e) {
            // fail-open
        }

        // Resolve "RECEIVED FROM" information (prefer Payee for non-student payments)
        $rfName = '';
        $rfTin = '';
        $rfAddress = '';
        $accountNo = '';

        // Payee branch (non-student)
        if ($payeeId) {
            try {
                $p = DB::table('tb_mas_payee')->where('id', (int) $payeeId)->first();
                if ($p) {
                    $ln = isset($p->lastname) ? trim((string)$p->lastname) : '';
                    $fn = isset($p->firstname) ? trim((string)$p->firstname) : '';
                    $mn = isset($p->middlename) ? trim((string)$p->middlename) : '';
                    $rfName = trim($ln . ', ' . $fn . ' ' . $mn);
                    if ($rfName === ',' || $rfName === '') {
                        $rfName = trim(($fn . ' ' . $mn . ' ' . $ln));
                    }
                    $rfAddress = isset($p->address) ? (string) $p->address : '';
                    $rfTin = isset($p->tin) ? (string) $p->tin : '';
                    // Account number for payee: use id_number
                    $accountNo = isset($p->id_number) ? (string) $p->id_number : '';
                }
            } catch (\Throwable $e) {
                // ignore payee lookup
            }
        }

        // Student branch (fallback)
        if (!$payeeId && $studentId) {
            try {
                $u = DB::table('tb_mas_users')->where('intID', $studentId)->first();
                if ($u) {
                    // Common columns in tb_mas_users
                    $ln = isset($u->strLastname) ? trim((string)$u->strLastname) : '';
                    $fn = isset($u->strFirstname) ? trim((string)$u->strFirstname) : '';
                    $mn = isset($u->strMiddlename) ? trim((string)$u->strMiddlename) : '';
                    $rfName = trim($ln . ', ' . $fn . ' ' . $mn);
                    if ($rfName === ',' || $rfName === '') {
                        // Fallback to any name-like fields if present
                        $rfName = trim((string)($u->strName ?? ''));
                    }
                    $rfAddress = isset($u->strAddress) ? (string)$u->strAddress : '';
                    // No stable TIN field in legacy tables; leave blank
                    $rfTin = '';
                    // Account number for student: student number
                    $accountNo = $studentNumber ?: '';
                }
            } catch (\Throwable $e) {
                // ignore user lookup
            }
        }

        // Aggregate OR items/total from tb_mas_transactions (preferred)
        $items = [];
        $total = 0.0;
        $orAgg = $this->finance->orLookup($orStr);
        if ($orAgg !== null) {
            foreach ($orAgg['items'] as $it) {
                $items[] = [
                    'description' => (string) ($it['type'] ?? ''),
                    'amount'      => (float) ($it['amount'] ?? 0),
                ];
            }
            $total = (float) ($orAgg['total'] ?? 0);
            if (!$postedAt && !empty($orAgg['date'])) {
                $postedAt = (string) $orAgg['date'];
            }
        } elseif ($pdDesc !== null && $pdAmount !== null) {
            // Fallback to payment_details single item
            $items[] = [
                'description' => $pdDesc,
                'amount'      => (float) $pdAmount,
            ];
            $total = (float) $pdAmount;
        }

        // Build DTO for renderer
        $dto = [
            'company_name'  => $companyName,
            'company_lines' => $companyLines,
            'company_tin'   => $companyTin,

            'or_no'         => $orStr,
            'payment_date'  => $postedAt ?: '',
            'account_no'    => $accountNo ?: '',
            'method'        => $method ?: '',

            'received_from_name'    => strtoupper($rfName),
            'received_from_tin'     => $rfTin,
            'received_from_address' => strtoupper($rfAddress),

            'items'         => $items,
            'total'         => (float) $total,
            'invoice_ref_no'=> $invoiceRefNo ?: '',
            'received_by_name' => (string) ($request->header('X-Authorized-Name') ?? ''),
        ];

        // Render and stream inline
        $renderer = app(OfficialReceiptPdf::class);
        $pdf = $renderer->render($dto);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="or-' . $orStr . '.pdf"',
        ]);
    }

    /**
     * GET /api/v1/finance/transactions
     * Query params:
     *  - student_number?: string
     *  - registration_id?: int
     *  - syid?: int
     *
     * Returns a list of transactions filtered by student number or registration id (and optional term).
     * Ordered by dtePaid, intORNumber (parity with CI).
     */
    public function transactions(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number'  => 'sometimes|string',
            'registration_id' => 'sometimes|integer',
            'syid'            => 'sometimes|integer',
        ]);

        $rows = $this->finance->listTransactions(
            $payload['student_number'] ?? null,
            isset($payload['registration_id']) ? (int)$payload['registration_id'] : null,
            isset($payload['syid']) ? (int)$payload['syid'] : null
        );

        return response()->json([
            'success' => true,
            'data'    => TransactionResource::collection($rows),
        ]);
    }

    /**
     * GET /api/v1/finance/or-lookup
     * Query params:
     *  - or: int|string (OR number)
     *
     * Returns aggregated OR breakdown similar to CI get_transaction_ajax but JSON:
     * {
     *   success: true,
     *   data: {
     *     or_no: string|int,
     *     date: string|null,
     *     items: [{ type, amount }],
     *     total: float
     *   }
     * }
     */
    public function orLookup(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'or' => 'required'
        ]);

        $result = $this->finance->orLookup($payload['or']);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'OR not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * GET /api/v1/finance/payment-details
     * Query params:
     *  - student_number?: string (optional when student_id is provided)
     *  - student_id?: int (preferred; compared to payment_details.student_information_id)
     *  - term: int (required) - syid
     *
     * Returns payment_details rows for the student's registration in the selected term,
     * including normalized items, sy_label, and meta totals.
     */
    public function paymentDetails(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number' => 'sometimes|nullable|string',
            'student_id'     => 'sometimes|nullable|integer',
            'term'           => 'required|integer',
        ]);

        $data = $this->finance->listPaymentDetails(
            $payload['student_number'] ?? null,
            (int) $payload['term'],
            isset($payload['student_id']) ? (int) $payload['student_id'] : null
        );

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/finance/cashier/viewer-data
     * Query params:
     *  - student_number: string (required)
     *  - term: int (required)
     *  - student_id?: int (optional fallback)
     */
    public function viewerData(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number' => 'sometimes|nullable|string',
            'term'           => 'required|integer',
            'student_id'     => 'sometimes|nullable|integer',
        ]);

        // Require at least one of student_number or student_id
        $studentNumber = isset($payload['student_number']) ? (string) $payload['student_number'] : null;
        $studentId = array_key_exists('student_id', $payload) ? (int) $payload['student_id'] : null;

        if ((empty($studentNumber) || $studentNumber === '') && empty($studentId)) {
            return response()->json([
                'success' => false,
                'message' => 'Either student_number or student_id is required.'
            ], 422);
        }

        // Resolve student_number when only student_id is provided
        if ((empty($studentNumber) || $studentNumber === '') && $studentId) {
            $sn = DB::table('tb_mas_users')
                ->where('intID', $studentId)
                ->value('strStudentNumber');
            $studentNumber = $sn ? (string) $sn : '';
        }

        /** @var \App\Services\CashierViewerAggregateService $svc */
        $svc = app(\App\Services\CashierViewerAggregateService::class);

        $data = $svc->buildByStudentNumber(
            (string) $studentNumber,
            (int) $payload['term'],
            $studentId ?: null
        );

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/finance/student-ledger
     * Query:
     *  - student_number?: string
     *  - student_id?: int
     *  - term: 'all' | int
     *  - sort?: 'asc'|'desc'
     *
     * Returns a unified ledger-like list composed from Saved Tuition (assessment),
     * Student Billing (charges/credits), and Paid Payment Details (payments).
     */
    public function studentLedger(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number' => 'sometimes|nullable|string',
            'student_id'     => 'sometimes|nullable|integer',
            'term'           => 'required',
            'sort'           => 'sometimes|in:asc,desc',
        ]);

        $termParam = $payload['term'];
        $term = (is_string($termParam) && strtolower($termParam) === 'all') ? 'all' : (int) $termParam;
        $sort = isset($payload['sort']) ? (string) $payload['sort'] : 'asc';

        /** @var StudentLedgerService $svc */
        $svc = app(\App\Services\StudentLedgerService::class);

        $data = $svc->getLedger(
            $payload['student_number'] ?? null,
            isset($payload['student_id']) ? (int)$payload['student_id'] : null,
            $term,
            $sort
        );

        // Augment ledger with any applied excess payment applications
        try {
            $excessSvc = app(\App\Services\ExcessPaymentService::class);
            $data = $excessSvc->augmentLedger($data, $sort);
        } catch (\Throwable $e) {
            // fail-open: if augmentation fails, return base ledger
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/v1/finance/ledger/excess/apply
     * Body:
     *  - student_id: int (required)
     *  - source_term_id: int (required)
     *  - target_term_id: int (required)
     *  - amount: number (required, > 0)
     *  - notes?: string
     */
    public function applyExcessPayment(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_id'     => 'required|integer',
            'source_term_id' => 'required|integer',
            'target_term_id' => 'required|integer',
            'amount'         => 'required|numeric|min:0.01',
            'notes'          => 'sometimes|nullable|string',
        ]);

        try {
            $actorId = (int) ($request->header('X-Faculty-ID') ?? 0) ?: null;
            $svc = app(\App\Services\ExcessPaymentService::class);
            $app = $svc->applyExcessPayment(
                (int) $payload['student_id'],
                (int) $payload['source_term_id'],
                (int) $payload['target_term_id'],
                (float) $payload['amount'],
                $actorId,
                $payload['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'data'    => $app,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply excess payment',
            ], 500);
        }
    }

    /**
     * POST /api/v1/finance/ledger/excess/revert
     * Body:
     *  - application_id: int (required)
     *  - notes?: string
     */
    public function revertExcessPayment(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'application_id' => 'required|integer',
            'notes'          => 'sometimes|nullable|string',
        ]);

        try {
            $actorId = (int) ($request->header('X-Faculty-ID') ?? 0) ?: null;
            $svc = app(\App\Services\ExcessPaymentService::class);
            $app = $svc->revertExcessPayment(
                (int) $payload['application_id'],
                $actorId,
                $payload['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'data'    => $app,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert excess payment',
            ], 500);
        }
    }
}
