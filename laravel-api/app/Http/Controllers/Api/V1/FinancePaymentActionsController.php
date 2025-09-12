<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FinancePaymentActionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancePaymentActionsController extends Controller
{
    protected FinancePaymentActionsService $actions;

    public function __construct(FinancePaymentActionsService $actions)
    {
        $this->actions = $actions;
    }

    /**
     * GET /api/v1/finance/payment-actions/search
     * Roles: finance_admin, admin
     *
     * Query (at least one of or_number or invoice_number is required; service enforces this as well):
     *  - or_number?: string
     *  - invoice_number?: string
     *  - student_number?: string
     *  - student_id?: int
     *  - syid?: int
     *  - status?: string
     *  - page?: int (default 1)
     *  - per_page?: int (default 20, max 200)
     */
    public function search(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'or_number'      => 'sometimes|string',
            'invoice_number' => 'sometimes|string',
            'student_number' => 'sometimes|string',
            'student_id'     => 'sometimes|integer',
            'syid'           => 'sometimes|integer',
            'status'         => 'sometimes|string',
            'page'           => 'sometimes|integer|min:1',
            'per_page'       => 'sometimes|integer|min:1|max:200',
        ]);

        $page    = (int) ($payload['page'] ?? 1);
        $perPage = (int) ($payload['per_page'] ?? 20);

        $filters = $payload;
        unset($filters['page'], $filters['per_page']);

        $result = $this->actions->search($filters, $page, $perPage);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * POST /api/v1/finance/payment-actions/{id}/void
     * Roles: finance_admin, admin
     *
     * Body:
     *  - remarks?: string
     */
    public function void(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'remarks' => 'sometimes|nullable|string',
        ]);

        $updated = $this->actions->void($id, $request);

        return response()->json([
            'success' => true,
            'data'    => $updated,
        ]);
    }

    /**
     * DELETE /api/v1/finance/payment-actions/{id}/retract
     * Roles: finance_admin, admin
     *
     * Hard delete the payment_details row; audit preserved in SystemLog via service.
     */
    public function retract(int $id, Request $request): JsonResponse
    {
        $this->actions->retract($id, $request);

        return response()->json([
            'success' => true,
            'message' => "payment_details id {$id} retracted",
        ]);
    }
}
