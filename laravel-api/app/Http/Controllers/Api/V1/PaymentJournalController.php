<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PaymentJournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentJournalController extends Controller
{
    public function __construct(private PaymentJournalService $svc)
    {
        // Routes protected via middleware('role:finance,admin') in api.php
    }

    /**
     * POST /api/v1/finance/payment-details/debit
     * Body:
     *  - student_id: int (required)
     *  - term: int (required)
     *  - amount: number (required, > 0) -> stored as negative subtotal_order
     *  - description: string (required)
     *  - remarks?: string
     *  - method?: string
     *  - posted_at?: string (ISO)
     *  - campus_id?: int
     *  - mode_of_payment_id?: int
     *  - invoice_id?: int
     *  - invoice_number?: int
     */
    public function debit(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_id'        => 'required|integer',
            'term'              => 'required|integer',
            'amount'            => 'required|numeric|min:0.01',
            'description'       => 'required|string',
            'remarks'           => 'sometimes|nullable|string',
            'method'            => 'sometimes|nullable|string',
            'posted_at'         => 'sometimes|nullable|string',
            'campus_id'         => 'sometimes|nullable|integer',
            'mode_of_payment_id'=> 'sometimes|nullable|integer',
            'invoice_id'        => 'sometimes|nullable|integer',
            'invoice_number'    => 'sometimes|nullable|integer',
        ]);

        $row = $this->svc->createDebit($payload, $request);

        return response()->json([
            'success' => true,
            'data'    => array_merge($row, ['entry_type' => 'debit']),
        ], 201);
    }

    /**
     * POST /api/v1/finance/payment-details/credit
     * Body:
     *  - student_id: int (required)
     *  - term: int (required)
     *  - amount: number (required, > 0) -> stored as positive subtotal_order
     *  - description: string (required)
     *  - remarks?: string
     *  - method?: string
     *  - posted_at?: string (ISO)
     *  - campus_id?: int
     *  - mode_of_payment_id?: int
     *  - invoice_id?: int
     *  - invoice_number?: int
     *  - enforce_invoice_remaining?: bool (default true)
     */
    public function credit(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_id'                 => 'required|integer',
            'term'                       => 'required|integer',
            'amount'                     => 'required|numeric|min:0.01',
            'description'                => 'required|string',
            'remarks'                    => 'sometimes|nullable|string',
            'method'                     => 'sometimes|nullable|string',
            'posted_at'                  => 'sometimes|nullable|string',
            'campus_id'                  => 'sometimes|nullable|integer',
            'mode_of_payment_id'         => 'sometimes|nullable|integer',
            'invoice_id'                 => 'sometimes|nullable|integer',
            'invoice_number'             => 'sometimes|nullable|integer',
            'enforce_invoice_remaining'  => 'sometimes|boolean',
        ]);

        $row = $this->svc->createCredit($payload, $request);

        return response()->json([
            'success' => true,
            'data'    => array_merge($row, ['entry_type' => 'credit']),
        ], 201);
    }
}
