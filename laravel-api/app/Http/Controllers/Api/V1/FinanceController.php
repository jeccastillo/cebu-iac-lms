<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FinanceService;
use App\Services\StudentLedgerService;

class FinanceController extends Controller
{
    protected FinanceService $finance;

    public function __construct(FinanceService $finance)
    {
        $this->finance = $finance;
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
