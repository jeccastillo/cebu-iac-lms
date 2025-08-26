<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FinanceService;

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
}
