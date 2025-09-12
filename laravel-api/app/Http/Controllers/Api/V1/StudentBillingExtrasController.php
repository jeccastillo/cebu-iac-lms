<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\StudentBillingExtrasService;
use Illuminate\Support\Facades\Auth;
use App\Services\UserContextResolver;

class StudentBillingExtrasController extends Controller
{
    protected StudentBillingExtrasService $extrasService;

    public function __construct(StudentBillingExtrasService $extrasService)
    {
        $this->extrasService = $extrasService;
        $this->middleware('role:finance,admin');
    }

    /**
     * GET /api/v1/finance/student-billing/missing-invoices
     * Query params: student_number (string), term (int)
     */
    public function missingInvoices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string'],
            'term'           => ['required', 'integer'],
        ]);

        $studentNumber = $validated['student_number'];
        $term = (int) $validated['term'];

        $missing = $this->extrasService->listMissingInvoices($studentNumber, $term);

        return response()->json([
            'success' => true,
            'data'    => $missing,
            'count'   => count($missing),
        ]);
    }

    /**
     * POST /api/v1/finance/student-billing/{id}/generate-invoice
     * Body: optional posted_at, remarks
     */
    public function generateInvoice(int $id, Request $request): JsonResponse
    {
        $billing = $this->extrasService->getBilling($id);
        if (!$billing) {
            return response()->json([
                'success' => false,
                'message' => "Billing item id {$id} not found",
            ], 404);
        }

        // Prefer Laravel Auth, but fall back to resolver (headers/session) when null
        $user = Auth::user();
        $actorId = $user ? $user->id : null;
        if ($actorId === null) {
            try {
                $resolver = app(UserContextResolver::class);
                $actorId = $resolver->resolveUserId($request);
            } catch (\Throwable $e) {
                // non-fatal; actorId stays null
            }
        }

        // Validate billing is un-invoiced
        if ($this->extrasService->hasInvoice($billing)) {
            return response()->json([
                'success' => false,
                'message' => "Billing item id {$id} already has an invoice",
            ], 422);
        }

        // Validate ownership and term consistency
        $studentId = (int)($billing['student_id'] ?? 0);
        $term = (int)($billing['syid'] ?? 0);
        if ($studentId <= 0 || $term <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid billing record: missing student or term.',
            ], 422);
        }

        $payload = $request->only(['posted_at', 'remarks']);

        try {
            $invoice = $this->extrasService->generateInvoiceForBilling($id, $studentId, $term, $payload, $actorId);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => "Failed to generate invoice: " . $ex->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => $invoice,
        ], 201);
    }
}
