<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StudentBillingStoreRequest;
use App\Http\Requests\Api\V1\StudentBillingUpdateRequest;
use App\Services\StudentBillingService;
use App\Services\TuitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Cashier;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use App\Services\SystemLogService;

class StudentBillingController extends Controller
{
    protected StudentBillingService $service;

    public function __construct(StudentBillingService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/finance/student-billing
     * Query:
     *  - student_number?: string (optional when student_id is provided)
     *  - student_id?: int
     *  - term: int (syid) required
     */
    public function index(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number' => ['sometimes', 'nullable', 'string'],
            'student_id'     => ['sometimes', 'nullable', 'integer'],
            'term'           => ['required', 'integer'],
        ]);

        $items = $this->service->list(
            $payload['student_number'] ?? null,
            isset($payload['student_id']) ? (int) $payload['student_id'] : null,
            (int) $payload['term']
        );

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    /**
     * GET /api/v1/finance/student-billing/{id}
     */
    public function show(int $id): JsonResponse
    {
        $item = $this->service->get($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Student billing item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * POST /api/v1/finance/student-billing
     * Body (StudentBillingStoreRequest):
     *  - student_id: int
     *  - term: int (syid)
     *  - description: string
     *  - amount: number (can be negative; not 0)
     *  - posted_at?: datetime
     *  - remarks?: string
     *  - generate_invoice?: boolean (default: true)
     */
    public function store(StudentBillingStoreRequest $request, TuitionService $tuition): JsonResponse
    {
        // StudentBillingStoreRequest->validated() already normalizes intStudentID and syid
        $data = $request->validated();
 
        // Actor id from middleware-injected faculty when available
        $faculty = $request->attributes->get('faculty');
        $actorId = $faculty ? (int) ($faculty->intID ?? null) : null;

        // Resolve acting cashier from faculty context
        $cashier = null;
        if (!empty($actorId)) {
            $cashier = Cashier::query()->where('faculty_id', (int) $actorId)->first();
        }

        // Enforce invoice number availability only when generating invoice
        if (($data['generate_invoice'] ?? true)) {
            if (!$cashier) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NO_CASHIER',
                    'message' => 'Cashier account is required to generate an invoice.',
                ], 422);
            }
            if (empty($cashier->invoice_current)) {
                return response()->json([
                    'success' => false,
                    'code'    => 'NO_CASHIER_INVOICE_CURRENT',
                    'message' => 'Cashier current invoice is not set. Cannot generate an invoice.',
                ], 422);
            }
        }

        // Create billing and (optionally) generate invoice atomically
        $item = DB::transaction(function () use ($data, $actorId, $cashier) {
            // 1) Create billing item
            $created = $this->service->create($data, $actorId);

            // 2) Optionally generate invoice for this billing
            $shouldGenerate = (bool) ($data['generate_invoice'] ?? true);
            if ($shouldGenerate) {
                $options = [
                    'invoice_number' => (int) $cashier->invoice_current,
                    'cashier_id'     => (int) $cashier->intID,
                    'posted_at'      => $data['posted_at'] ?? null,
                    'remarks'        => $data['remarks'] ?? null,
                    'items'          => [
                        ['description' => (string) $data['description'], 'amount' => round((float) $data['amount'], 2)],
                    ],
                    'amount'         => round((float) $data['amount'], 2),
                    'status'         => 'Draft',
                    // Extra invoice fields (optional)
                    'withholding_tax_percentage' => $data['withholding_tax_percentage'] ?? null,
                    'invoice_amount'             => $data['invoice_amount'] ?? null,
                    'invoice_amount_ves'         => $data['invoice_amount_ves'] ?? null,
                    'invoice_amount_vzrs'        => $data['invoice_amount_vzrs'] ?? null,
                ];

                app(InvoiceService::class)->generate(
                    (string) $data['description'],
                    (int) $data['intStudentID'],
                    (int) $data['syid'],
                    $options,
                    $actorId
                );

                // 3) Advance cashier invoice pointer
                if (!empty($options['invoice_number'])) {
                    Cashier::query()
                        ->where('intID', (int) $cashier->intID)
                        ->increment('invoice_current');
                }
            }

            return $created;
        });
        
        // After adding a billing item, save tuition snapshot for this student+term.
        // Do not fail the request if snapshot save throws; keep response schema unchanged.
        try {
            $tuition->saveSnapshotByStudentId((int) $data['intStudentID'], (int) $data['syid'], $actorId);
        } catch (\Throwable $e) {
            // No-op: intentionally suppress to avoid blocking billing creation
        }
 
        // System log: create billing
        SystemLogService::log('create', 'StudentBilling', (int) ($item['id'] ?? 0), null, $item, $request);

        return response()->json([
            'success' => true,
            'data'    => $item,
        ], 201);
    }

    /**
     * PUT /api/v1/finance/student-billing/{id}
     * Body (StudentBillingUpdateRequest):
     *  - description?: string
     *  - amount?: number (can be negative; not 0)
     *  - posted_at?: datetime
     *  - remarks?: string
     */
    public function update(int $id, StudentBillingUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        // Actor id from middleware-injected faculty when available
        $faculty = $request->attributes->get('faculty');
        $actorId = $faculty ? (int) ($faculty->intID ?? null) : null;

        $old = $this->service->get($id);
        $item = $this->service->update($id, $data, $actorId);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Student billing item not found',
            ], 404);
        }

        // System log: update billing
        SystemLogService::log('update', 'StudentBilling', (int) $id, $old, $item, $request);

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    /**
     * DELETE /api/v1/finance/student-billing/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $existing = $this->service->get($id);
        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Student billing item not found',
            ], 404);
        }

        $this->service->delete($id);

        // System log: delete billing
        SystemLogService::log('delete', 'StudentBilling', (int) $id, $existing, null, request());

        return response()->json([
            'success' => true,
            'message' => 'Student billing item deleted',
        ]);
    }
}
