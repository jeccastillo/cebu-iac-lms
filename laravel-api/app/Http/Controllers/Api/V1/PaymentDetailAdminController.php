<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaymentDetailUpdateRequest;
use App\Services\PaymentDetailAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentDetailAdminController extends Controller
{
    protected PaymentDetailAdminService $payments;

    public function __construct(PaymentDetailAdminService $payments)
    {
        $this->payments = $payments;
    }

    /**
     * GET /api/v1/finance/payment-details/admin
     *
     * Admin-only search with filters and pagination.
     * Query params (all optional unless noted):
     *  - q: string
     *  - student_number: string
     *  - student_id: int
     *  - syid: int
     *  - status: string
     *  - mode: string ('or'|'invoice') [hint only]
     *  - or_number: string
     *  - invoice_number: string
     *  - date_from: date (Y-m-d or ISO)
     *  - date_to: date (Y-m-d or ISO)
     *  - page: int (default 1)
     *  - per_page: int (default 20)
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'q'               => 'sometimes|string',
            'student_number'  => 'sometimes|string',
            'student_id'      => 'sometimes|integer',
            'syid'            => 'sometimes|integer',
            'status'          => 'sometimes|string',
            'mode'            => 'sometimes|string',
            'or_number'       => 'sometimes|string',
            'invoice_number'  => 'sometimes|string',
            'date_from'       => 'sometimes|date',
            'date_to'         => 'sometimes|date',
            'page'            => 'sometimes|integer|min:1',
            'per_page'        => 'sometimes|integer|min:1|max:200',
        ]);

        $page = (int) ($payload['page'] ?? 1);
        $perPage = (int) ($payload['per_page'] ?? 20);

        // Remove pagination keys from filters
        $filters = $payload;
        unset($filters['page'], $filters['per_page']);

        $result = $this->payments->search($filters, $page, $perPage);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * GET /api/v1/finance/payment-details/{id}
     * Admin-only single item fetch.
     */
    public function show(int $id): JsonResponse
    {
        $row = $this->payments->getById($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => "payment_details id {$id} not found",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $row,
        ]);
    }

    /**
     * PATCH /api/v1/finance/payment-details/{id}
     * Admin-only update.
     */
    public function update(int $id, PaymentDetailUpdateRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $updated = $this->payments->update($id, $payload, $request);

        return response()->json([
            'success' => true,
            'data'    => $updated,
        ]);
    }

    /**
     * DELETE /api/v1/finance/payment-details/{id}
     * Admin-only hard delete.
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $this->payments->delete($id, $request);

        return response()->json([
            'success' => true,
            'message' => "payment_details id {$id} deleted",
        ]);
    }
}
