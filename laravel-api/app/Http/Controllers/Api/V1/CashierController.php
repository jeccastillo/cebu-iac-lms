<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CashierPaymentAssignNumberRequest;
use App\Http\Requests\Api\V1\CashierPaymentStoreRequest;
use App\Http\Requests\Api\V1\CashierRangeUpdateRequest;
use App\Http\Requests\Api\V1\CashierStoreRequest;
use App\Http\Requests\Api\V1\CashierUpdateRequest;
use App\Models\Cashier;
use App\Services\CashierService;
use App\Services\PaymentDetailAdminService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CashierController extends Controller
{
    public function __construct(private CashierService $svc)
    {
        // Routes will be protected via middleware('role:cashier_admin,admin') in api.php
    }

    /**
     * GET /api/v1/cashiers
     * Optional: ?includeStats=1
     */
    public function index(Request $request)
    {
        $includeStats = (bool)$request->boolean('includeStats', false);
        $campusId = $request->input('campus_id');

        $q = Cashier::query()
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'tb_mas_cashiers.faculty_id')
            ->when(!is_null($campusId), fn($qq) => $qq->where('tb_mas_cashiers.campus_id', $campusId))
            ->select(
                'tb_mas_cashiers.*',
                DB::raw("CONCAT(COALESCE(f.strFirstname,''),' ',COALESCE(f.strLastname,'')) as name")
            )
            ->orderBy('tb_mas_cashiers.intID', 'asc');

        $rows = $q->get();

        $data = $rows->map(function ($r) use ($includeStats) {
            $base = [
                'id'         => (int)$r->intID,
                'user_id'    => (int)$r->user_id,
                'faculty_id' => $r->faculty_id !== null ? (int)$r->faculty_id : null,
                'name'       => $r->name ?? null,
                'campus_id'  => $r->campus_id !== null ? (int)$r->campus_id : null,
                'temporary_admin' => (int)($r->temporary_admin ?? 0),
                'or' => [
                    'start'   => $r->or_start !== null ? (int)$r->or_start : null,
                    'end'     => $r->or_end !== null ? (int)$r->or_end : null,
                    'current' => $r->or_current !== null ? (int)$r->or_current : null,
                ],
                'invoice' => [
                    'start'   => $r->invoice_start !== null ? (int)$r->invoice_start : null,
                    'end'     => $r->invoice_end !== null ? (int)$r->invoice_end : null,
                    'current' => $r->invoice_current !== null ? (int)$r->invoice_current : null,
                ],
            ];

            if ($includeStats) {
                $stats = $this->svc->computeStats($r);
                $base['stats'] = $stats;
            }

            return $base;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/v1/cashiers
     * Create a cashier row with initial ranges. Auto-resets current to start.
     */
    public function store(CashierStoreRequest $request)
    {
        $payload = $request->validated();

        $facultyId = (int)$payload['faculty_id'];
        $campusId = (int)$payload['campus_id'];

        // Validate faculty exists and campus matches
        $faculty = DB::table('tb_mas_faculty')->select('intID', 'campus_id')->where('intID', $facultyId)->first();
        if (!$faculty) {
            throw ValidationException::withMessages([
                'faculty_id' => ['Faculty not found']
            ]);
        }
        if (!isset($faculty->campus_id) || (int)$faculty->campus_id !== (int)$campusId) {
            throw ValidationException::withMessages([
                'faculty_id' => ['Faculty campus must match cashier campus']
            ]);
        }

        // Enforce uniqueness per campus: (campus_id, faculty_id)
        $conflict = Cashier::query()
            ->where('campus_id', $campusId)
            ->where('faculty_id', $facultyId)
            ->exists();
        if ($conflict) {
            throw ValidationException::withMessages([
                'faculty_id' => ['Faculty is already assigned as cashier for this campus']
            ]);
        }

        // Validate OR overlap & usage
        if (isset($payload['or_start'], $payload['or_end'])) {
            $orStart = (int)$payload['or_start'];
            $orEnd   = (int)$payload['or_end'];

            $res = $this->svc->validateRangeOverlap('or', $orStart, $orEnd, null, $campusId);
            if (!$res['ok']) {
                throw ValidationException::withMessages([
                    'or' => ['Overlap with another cashier range', $res['conflict']]
                ]);
            }

            $usage = $this->svc->validateRangeUsage('or', $orStart, $orEnd);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'or' => ['Range includes used OR number', $usage]
                ]);
            }
        }

        // Validate Invoice overlap & usage
        if (isset($payload['invoice_start'], $payload['invoice_end'])) {
            $invStart = (int)$payload['invoice_start'];
            $invEnd   = (int)$payload['invoice_end'];

            $res = $this->svc->validateRangeOverlap('invoice', $invStart, $invEnd, null, $campusId);
            if (!$res['ok']) {
                throw ValidationException::withMessages([
                    'invoice' => ['Overlap with another cashier range', $res['conflict']]
                ]);
            }

            $usage = $this->svc->validateRangeUsage('invoice', $invStart, $invEnd);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'invoice' => ['Range includes used Invoice number', $usage]
                ]);
            }
        }

        $row = new Cashier();
        $row->faculty_id      = $facultyId;
        $row->campus_id       = $campusId;
        $row->temporary_admin = (int)($payload['temporary_admin'] ?? 0);

        $row->or_start = $payload['or_start'] ?? null;
        $row->or_end   = $payload['or_end'] ?? null;
        $row->or_current = isset($payload['or_start']) ? (int)$payload['or_start'] : null;

        $row->invoice_start   = $payload['invoice_start'] ?? null;
        $row->invoice_end     = $payload['invoice_end'] ?? null;
        $row->invoice_current = isset($payload['invoice_start']) ? (int)$payload['invoice_start'] : null;

        $row->save();

        return response()->json([
            'success' => true,
            'data'    => [
                'id' => (int)$row->intID
            ]
        ], 201);
    }

    /**
     * PATCH /api/v1/cashiers/{id}
     * Update temporary_admin or current pointers (with bounds checks).
     */
    public function update($id, CashierUpdateRequest $request)
    {
        $row = Cashier::findOrFail((int)$id);
        $payload = $request->validated();

        if (isset($payload['temporary_admin'])) {
            $row->temporary_admin = (int)$payload['temporary_admin'];
        }

        // Update current pointers (ensure within bounds if ranges exist)
        if (isset($payload['or_current'])) {
            $orCurrent = (int)$payload['or_current'];
            $orStart = (int)($row->or_start ?? 0);
            $orEnd   = (int)($row->or_end ?? 0);

            if ($orStart > 0 && $orEnd > 0 && !$this->svc->currentWithinRange($orCurrent, $orStart, $orEnd)) {
                throw ValidationException::withMessages([
                    'or_current' => ['or_current must be within [or_start, or_end]']
                ]);
            }
            $row->or_current = $orCurrent;
        }

        if (isset($payload['invoice_current'])) {
            $invCurrent = (int)$payload['invoice_current'];
            $invStart = (int)($row->invoice_start ?? 0);
            $invEnd   = (int)($row->invoice_end ?? 0);

            if ($invStart > 0 && $invEnd > 0 && !$this->svc->currentWithinRange($invCurrent, $invStart, $invEnd)) {
                throw ValidationException::withMessages([
                    'invoice_current' => ['invoice_current must be within [invoice_start, invoice_end]']
                ]);
            }
            $row->invoice_current = $invCurrent;
        }

        $row->save();

        return response()->json([
            'success' => true,
            'data' => ['id' => (int)$row->intID]
        ]);
    }

    /**
     * POST /api/v1/cashiers/{id}/ranges
     * Update OR/Invoice ranges; auto-reset current to start; validate overlap and prior usage.
     */
    public function updateRanges($id, CashierRangeUpdateRequest $request)
    {
        $row = Cashier::findOrFail((int)$id);
        $payload = $request->validated();
        $campusId = $payload['campus_id'] ?? $row->campus_id;

        DB::beginTransaction();
        try {
            if (isset($payload['or_start'], $payload['or_end'])) {
                $orStart = (int)$payload['or_start'];
                $orEnd   = (int)$payload['or_end'];

                $res = $this->svc->validateRangeOverlap('or', $orStart, $orEnd, (int)$row->intID, $campusId);
                if (!$res['ok']) {
                    throw ValidationException::withMessages([
                        'or' => ['Overlap with another cashier range', $res['conflict']]
                    ]);
                }

                $usage = $this->svc->validateRangeUsage('or', $orStart, $orEnd);
                if (!$usage['ok']) {
                    throw ValidationException::withMessages([
                        'or' => ['Range includes used OR number', $usage]
                    ]);
                }

                $row->or_start = $orStart;
                $row->or_end   = $orEnd;
                $row->or_current = $orStart; // auto-reset
            }

            if (isset($payload['invoice_start'], $payload['invoice_end'])) {
                $invStart = (int)$payload['invoice_start'];
                $invEnd   = (int)$payload['invoice_end'];

                $res = $this->svc->validateRangeOverlap('invoice', $invStart, $invEnd, (int)$row->intID, $campusId);
                if (!$res['ok']) {
                    throw ValidationException::withMessages([
                        'invoice' => ['Overlap with another cashier range', $res['conflict']]
                    ]);
                }

                $usage = $this->svc->validateRangeUsage('invoice', $invStart, $invEnd);
                if (!$usage['ok']) {
                    throw ValidationException::withMessages([
                        'invoice' => ['Range includes used Invoice number', $usage]
                    ]);
                }

                $row->invoice_start = $invStart;
                $row->invoice_end   = $invEnd;
                $row->invoice_current = $invStart; // auto-reset
            }

            if (isset($payload['campus_id'])) {
                $row->campus_id = $campusId;
            }

            $row->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => ['id' => (int)$row->intID]
            ]);
        } catch (\Throwable $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * PATCH /api/v1/cashiers/{id}/assign
     * Assign this cashier to a faculty (no unassignment allowed).
     * Enforces campus match and per-campus uniqueness.
     */
    public function assign($id, \App\Http\Requests\Api\V1\CashierAssignRequest $request)
    {
        $row = Cashier::findOrFail((int)$id);
        $payload = $request->validated();

        $facultyId = (int)$payload['faculty_id'];
        $campusId = (int)($row->campus_id ?? 0);
        if ($campusId <= 0) {
            throw ValidationException::withMessages([
                'campus_id' => ['Cashier has no campus_id; set ranges or campus before assignment']
            ]);
        }

        $faculty = DB::table('tb_mas_faculty')->select('intID', 'campus_id')->where('intID', $facultyId)->first();
        if (!$faculty) {
            throw ValidationException::withMessages([
                'faculty_id' => ['Faculty not found']
            ]);
        }
        if (!isset($faculty->campus_id) || (int)$faculty->campus_id !== $campusId) {
            throw ValidationException::withMessages([
                'faculty_id' => ['Faculty campus must match cashier campus']
            ]);
        }

        $conflict = Cashier::query()
            ->where('campus_id', $campusId)
            ->where('faculty_id', $facultyId)
            ->where('intID', '<>', (int)$row->intID)
            ->exists();
        if ($conflict) {
            throw ValidationException::withMessages([
                'faculty_id' => ['Faculty is already assigned as cashier for this campus']
            ]);
        }

        $row->faculty_id = $facultyId;
        $row->save();

        return response()->json([
            'success' => true,
            'data' => ['id' => (int)$row->intID]
        ]);
    }

    /**
     * GET /api/v1/cashiers/{id}/stats
     */
    public function stats($id)
    {
        $row = Cashier::findOrFail((int)$id);
        $stats = $this->svc->computeStats($row);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * GET /api/v1/cashiers/stats
     * Optional: page/perPage
     */
    public function statsAll(Request $request)
    {
        $q = Cashier::query()->orderBy('intID', 'asc');

        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(1, min(100, (int)$request->input('perPage', 25)));
        $total = $q->count();
        $rows = $q->forPage($page, $perPage)->get();

        $data = $rows->map(fn($r) => $this->svc->computeStats($r));

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total
            ]
        ]);
    }

    /**
     * GET /api/v1/cashiers/me
     * Resolve the acting cashier by header X-Faculty-ID (or request faculty_id).
     */
    public function me(Request $request)
    {
        $facultyId = (int) ($request->header('X-Faculty-ID', $request->input('faculty_id')));
        if ($facultyId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty context required'
            ], 400);
        }

        $r = Cashier::query()
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'tb_mas_cashiers.faculty_id')
            ->where('tb_mas_cashiers.faculty_id', $facultyId)
            ->select(
                'tb_mas_cashiers.*',
                DB::raw("CONCAT(COALESCE(f.strFirstname,''),' ',COALESCE(f.strLastname,'')) as name")
            )
            ->first();

        if (!$r) {
            return response()->json([
                'success' => false,
                'message' => 'Cashier not found for acting faculty'
            ], 404);
        }

        $data = [
            'id'         => (int)$r->intID,
            'user_id'    => (int)$r->user_id,
            'faculty_id' => $r->faculty_id !== null ? (int)$r->faculty_id : null,
            'name'       => $r->name ?? null,
            'campus_id'  => $r->campus_id !== null ? (int)$r->campus_id : null,
            'temporary_admin' => (int)($r->temporary_admin ?? 0),
            'or' => [
                'start'   => $r->or_start !== null ? (int)$r->or_start : null,
                'end'     => $r->or_end !== null ? (int)$r->or_end : null,
                'current' => $r->or_current !== null ? (int)$r->or_current : null,
            ],
            'invoice' => [
                'start'   => $r->invoice_start !== null ? (int)$r->invoice_start : null,
                'end'     => $r->invoice_end !== null ? (int)$r->invoice_end : null,
                'current' => $r->invoice_current !== null ? (int)$r->invoice_current : null,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * GET /api/v1/cashiers/{id}
     * Optional: ?includeStats=1
     */
    public function show($id, Request $request)
    {
        $includeStats = (bool)$request->boolean('includeStats', false);

        $r = Cashier::query()
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'tb_mas_cashiers.faculty_id')
            ->where('tb_mas_cashiers.intID', (int)$id)
            ->select(
                'tb_mas_cashiers.*',
                DB::raw("CONCAT(COALESCE(f.strFirstname,''),' ',COALESCE(f.strLastname,'')) as name")
            )
            ->first();

        if (!$r) {
            abort(404);
        }

        $data = [
            'id'         => (int)$r->intID,
            'user_id'    => (int)$r->user_id,
            'faculty_id' => $r->faculty_id !== null ? (int)$r->faculty_id : null,
            'name'       => $r->name ?? null,
            'campus_id'  => $r->campus_id !== null ? (int)$r->campus_id : null,
            'temporary_admin' => (int)($r->temporary_admin ?? 0),
            'or' => [
                'start'   => $r->or_start !== null ? (int)$r->or_start : null,
                'end'     => $r->or_end !== null ? (int)$r->or_end : null,
                'current' => $r->or_current !== null ? (int)$r->or_current : null,
            ],
            'invoice' => [
                'start'   => $r->invoice_start !== null ? (int)$r->invoice_start : null,
                'end'     => $r->invoice_end !== null ? (int)$r->invoice_end : null,
                'current' => $r->invoice_current !== null ? (int)$r->invoice_current : null,
            ],
        ];

        if ($includeStats) {
            $stats = $this->svc->computeStats($r);
            $data['stats'] = $stats;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * DELETE /api/v1/cashiers/{id}
     * Deletes a cashier row. Prevent deletion if ranges include used numbers.
     */
    public function destroy($id)
    {
        $row = Cashier::findOrFail((int)$id);

        // Prevent deletion if OR range overlaps any used OR number
        if (!is_null($row->or_start) && !is_null($row->or_end)) {
            $usage = $this->svc->validateRangeUsage('or', (int)$row->or_start, (int)$row->or_end);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'or' => ['Cannot delete: range includes used OR number', $usage]
                ]);
            }
        }

        // Prevent deletion if Invoice range overlaps any used Invoice number
        if (!is_null($row->invoice_start) && !is_null($row->invoice_end)) {
            $usage = $this->svc->validateRangeUsage('invoice', (int)$row->invoice_start, (int)$row->invoice_end);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'invoice' => ['Cannot delete: range includes used Invoice number', $usage]
                ]);
            }
        }

        $row->delete();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * POST /api/v1/cashiers/{cashier}/payments/{payment}/assign-number
     * Assign an OR or Invoice number to a payment that was created with mode='none'
     */
    public function assignNumber($cashierId, $paymentId, CashierPaymentAssignNumberRequest $request)
    {
        $cashier = Cashier::findOrFail((int)$cashierId);
        $payload = $request->validated();
        
        $type = (string) $payload['type']; // 'or' or 'invoice'
        $specificNumber = isset($payload['number']) ? (int) $payload['number'] : null;
        
        // Ensure payment_details table exists
        if (!Schema::hasTable('payment_details')) {
            throw ValidationException::withMessages([
                'payment_details' => ['payment_details table not found']
            ]);
        }
        
        // Get the payment record
        $payment = DB::table('payment_details')
            ->where('intID', (int)$paymentId)
            ->first();
            
        if (!$payment) {
            throw ValidationException::withMessages([
                'payment' => ['Payment not found']
            ]);
        }
        
        // Determine the number column based on type
        $numberCol = null;
        if ($type === 'invoice') {
            if (Schema::hasColumn('payment_details', 'invoice_number')) {
                $numberCol = 'invoice_number';
            }
        } else {
            if (Schema::hasColumn('payment_details', 'or_no')) {
                $numberCol = 'or_no';
            } elseif (Schema::hasColumn('payment_details', 'or_number')) {
                $numberCol = 'or_number';
            }
        }
        
        if ($numberCol === null) {
            throw ValidationException::withMessages([
                'type' => ['Number column not available in payment_details for selected type']
            ]);
        }
        
        // Check if payment already has a number assigned
        if (isset($payment->{$numberCol}) && $payment->{$numberCol} !== null && $payment->{$numberCol} !== '') {
            throw ValidationException::withMessages([
                'payment' => ['Payment already has a ' . strtoupper($type) . ' number assigned: ' . $payment->{$numberCol}]
            ]);
        }
        
        // Determine the number to assign
        $numberToAssign = null;
        
        if ($specificNumber !== null) {
            // Use specific number if provided
            $numberToAssign = $specificNumber;
            
            // Validate the number is within cashier's range
            $start = $type === 'invoice' ? (int) ($cashier->invoice_start ?? 0) : (int) ($cashier->or_start ?? 0);
            $end = $type === 'invoice' ? (int) ($cashier->invoice_end ?? 0) : (int) ($cashier->or_end ?? 0);
            
            if ($start <= 0 || $end <= 0 || $numberToAssign < $start || $numberToAssign > $end) {
                throw ValidationException::withMessages([
                    'number' => ['Number must be within cashier\'s configured range (' . $start . '-' . $end . ')']
                ]);
            }
            
            // Check if number is already used
            $usage = $this->svc->validateRangeUsage($type, $numberToAssign, $numberToAssign);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'number' => ['Number already used', $usage]
                ]);
            }
        } else {
            // Use next available number from cashier's sequence
            $start = $type === 'invoice' ? (int) ($cashier->invoice_start ?? 0) : (int) ($cashier->or_start ?? 0);
            $end = $type === 'invoice' ? (int) ($cashier->invoice_end ?? 0) : (int) ($cashier->or_end ?? 0);
            $current = $type === 'invoice' ? (int) ($cashier->invoice_current ?? 0) : (int) ($cashier->or_current ?? 0);
            
            if ($start <= 0 || $end <= 0 || $end < $start) {
                throw ValidationException::withMessages([
                    'range' => ['Cashier range is not properly configured for ' . strtoupper($type)]
                ]);
            }
            
            if ($current <= 0 || $current < $start || $current > $end) {
                throw ValidationException::withMessages([
                    'current' => ['Current pointer is not properly set for ' . strtoupper($type)]
                ]);
            }
            
            // Check if we have numbers available
            if ($current > $end) {
                throw ValidationException::withMessages([
                    'range' => ['No more numbers available in cashier\'s ' . strtoupper($type) . ' range']
                ]);
            }
            
            $numberToAssign = $current;
            
            // Validate the number is not already used
            $usage = $this->svc->validateRangeUsage($type, $numberToAssign, $numberToAssign);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'number' => ['Next number in sequence already used', $usage]
                ]);
            }
        }
        
        // Store old value for logging
        $oldValue = [
            $numberCol => null
        ];
        
        // Transaction: update payment and increment counter if using sequence
        DB::transaction(function () use ($payment, $numberCol, $numberToAssign, $cashier, $type, $specificNumber, $paymentId) {
            // Update the payment with the new number
            DB::table('payment_details')
                ->where('intID', (int)$paymentId)
                ->update([
                    $numberCol => $numberToAssign,
                    'updated_at' => now()
                ]);
            
            // If we used the cashier's sequence (not a specific number), increment the counter
            if ($specificNumber === null) {
                if ($type === 'invoice') {
                    $cashier->invoice_current = (int) $cashier->invoice_current + 1;
                } else {
                    $cashier->or_current = (int) $cashier->or_current + 1;
                }
                $cashier->save();
            }
        });
        
        // Get updated payment for logging
        $updatedPayment = DB::table('payment_details')
            ->where('intID', (int)$paymentId)
            ->first();
        
        $newValue = [
            $numberCol => $numberToAssign
        ];
        
        // System log: assign number to payment
        SystemLogService::log('assign_number', 'PaymentDetail', (int) $paymentId, $oldValue, $newValue, $request);
        
        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => (int) $paymentId,
                'type' => $type,
                'number_assigned' => $numberToAssign,
                'cashier_id' => (int) $cashier->intID,
                'auto_increment' => $specificNumber === null
            ],
            'message' => strtoupper($type) . ' number ' . $numberToAssign . ' successfully assigned to payment'
        ]);
    }

    /**
     * POST /api/v1/cashiers/{id}/payments
     * Create a payment_details row using the cashier's next OR/Invoice number and increment the pointer.
     */
    public function createPayment($id, CashierPaymentStoreRequest $request)
    {
        $row = Cashier::findOrFail((int)$id);
        $payload = $request->validated();

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        $studentId = (int) $payload['student_id'];
        $requestId = isset($payload['requestId']) ? $payload['convenience_fee'] : $randomString;
        $conFee  =   isset($payload['convenience_fee']) ? $payload['convenience_fee'] : 0;
        $syid      = (int) $payload['term']; // sy_reference will store SYID (per instruction)
        $modeIn    = (string) $payload['mode'];
        $mode      = in_array($modeIn, ['or','invoice','none'], true) ? $modeIn : 'or';
        $amount    = (float) $payload['amount'];
        $desc      = (string) $payload['description'];
        $remarks   = (string) $payload['remarks'];
        $methodIn  = isset($payload['method']) ? (string) $payload['method'] : null;
        $postedAt  = isset($payload['posted_at']) ? (string) $payload['posted_at'] : null;
        $campusId  = isset($payload['campus_id']) ? (int) $payload['campus_id'] : null;
        $modePaymentId = isset($payload['mode_of_payment_id']) ? (int) $payload['mode_of_payment_id'] : null;

        // Resolve selected invoice number up-front if provided in payload
        $invoiceNumberRef = null;
        if ((isset($payload['invoice_id']) || isset($payload['invoice_number']))) {
            try {
                $invoiceRow = null;
                if (isset($payload['invoice_id'])) {
                    $iid = (int) $payload['invoice_id'];
                    if ($iid > 0 && Schema::hasTable('tb_mas_invoices')) {
                        $invoiceRow = DB::table('tb_mas_invoices')
                            ->select('intID', 'invoice_number')
                            ->where(function ($qq) use ($iid) {
                                // support id or intID depending on schema
                                $qq->where('intID', $iid);
                            })
                            ->first();
                    }
                }
                if ($invoiceRow && isset($invoiceRow->invoice_number) && $invoiceRow->invoice_number !== null && $invoiceRow->invoice_number !== '') {
                    $invoiceNumberRef = (int) $invoiceRow->invoice_number;
                } elseif (isset($payload['invoice_number']) && $payload['invoice_number'] !== '') {
                    $invoiceNumberRef = (int) $payload['invoice_number'];
                }
            } catch (\Throwable $e) {
                // ignore resolution failures                
            }
        }
        
        // Ensure payment_details table and core columns exist
        if (!Schema::hasTable('payment_details')) {
            throw ValidationException::withMessages([
                'payment_details' => ['payment_details table not found']
            ]);
        }
        $core = ['student_information_id', 'sy_reference', 'description', 'subtotal_order', 'status'];
        foreach ($core as $c) {
            if (!Schema::hasColumn('payment_details', $c)) {
                throw ValidationException::withMessages([
                    'payment_details' => ["Missing required column payment_details.$c"]
                ]);
            }
        }

        // Determine number column based on mode
        $numberCol = null;
        if ($mode === 'invoice') {
            if (Schema::hasColumn('payment_details', 'invoice_number')) {
                $numberCol = 'invoice_number';
            }
        } else {
            if (Schema::hasColumn('payment_details', 'or_no')) {
                $numberCol = 'or_no';
            } elseif (Schema::hasColumn('payment_details', 'or_number')) {
                $numberCol = 'or_number';
            }
        }
        if ($numberCol === null && $mode !== 'none') {
            throw ValidationException::withMessages([
                'mode' => ['Number column not available in payment_details for selected mode']
            ]);
        }

        // Policy: if an invoice is selected and this is the first payment for that invoice,
        // skip assigning/consuming an OR number and just save with the invoice number.
        $skipOrAndUseInvoice = false;       
        
        if ($mode === 'or' && $invoiceNumberRef !== null && Schema::hasColumn('payment_details', 'invoice_number')) {
            try {
                $existingCount = (int) DB::table('payment_details')
                    ->where('invoice_number', $invoiceNumberRef)
                    ->count();                                    
                if ($existingCount === 0) {                                   
                    // First payment on this invoice: do not use OR number
                    $skipOrAndUseInvoice = true;
                    $numberCol = 'invoice_number'; // ensure we store the invoice number
                }
            } catch (\Throwable $e) {
                // if we cannot determine, do not skip to be safe                
            }
        }   
        // Determine pointers and range (skip when we're using invoice number for first payment or mode is none)
        $current = null;
        if ($mode !== 'none' && !$skipOrAndUseInvoice) {
            $start   = $mode === 'invoice' ? (int) ($row->invoice_start ?? 0) : (int) ($row->or_start ?? 0);
            $end     = $mode === 'invoice' ? (int) ($row->invoice_end ?? 0)   : (int) ($row->or_end ?? 0);
            $current = $mode === 'invoice' ? (int) ($row->invoice_current ?? 0) : (int) ($row->or_current ?? 0);

            if ($start <= 0 || $end <= 0 || $end < $start) {
                throw ValidationException::withMessages([
                    'range' => ['Cashier range is not properly configured']
                ]);
            }
            if ($current <= 0) {
                throw ValidationException::withMessages([
                    'current' => ['Current pointer is not set']
                ]);
            }
            if ($current < $start || $current > $end) {
                throw ValidationException::withMessages([
                    'current' => ['Current pointer must be within configured range']
                ]);
            }

            // Re-validate number usage for the single current number
            $usage = $this->svc->validateRangeUsage($mode, (int) $current, (int) $current);
            if (!$usage['ok']) {
                throw ValidationException::withMessages([
                    'number' => ['Selected number already used', $usage]
                ]);
            }
        }

        // Backend hardening: when a specific invoice is referenced in payload (invoice_id or invoice_number),
        // enforce that the amount does not exceed the remaining amount on that invoice.
        // Remaining = invoice.amount_total - SUM(payment_details.subtotal_order WHERE status='Paid' AND invoice_number=...).
        if ((isset($payload['invoice_id']) || isset($payload['invoice_number']))) {
            try {
                $invoiceRow = null;
                $invoiceNumberRef = null;
                $invoiceTotal = null;

                // Resolve invoice by id when provided
                if (isset($payload['invoice_id'])) {
                    $iid = (int) $payload['invoice_id'];
                    if ($iid > 0 && Schema::hasTable('tb_mas_invoices')) {
                        $invoiceRow = DB::table('tb_mas_invoices')
                            ->select('intID', 'invoice_number', 'amount_total','status')
                            ->where('intID', $iid)
                            ->first();
                    }
                }

                // Prefer invoice_number from row; fallback to payload
                if ($invoiceRow && isset($invoiceRow->invoice_number) && $invoiceRow->invoice_number !== null && $invoiceRow->invoice_number !== '') {
                    $invoiceNumberRef = (int) $invoiceRow->invoice_number;
                }
                if ($invoiceNumberRef === null && isset($payload['invoice_number']) && $payload['invoice_number'] !== '') {
                    $invoiceNumberRef = (int) $payload['invoice_number'];
                }

                // Determine invoice total (amount_total preferred; fallback amount/total)
                if ($invoiceRow) {
                    $cands = [
                        $invoiceRow->amount_total ?? null,
                        $invoiceRow->amount ?? null,
                        $invoiceRow->total ?? null,
                    ];
                    foreach ($cands as $cand) {
                        if ($cand !== null && is_numeric($cand)) {
                            $invoiceTotal = (float) $cand;
                            break;
                        }
                    }
                }

                // Validate only when we have both a number and a total, and when payment_details.invoice_number exists
                if ($invoiceNumberRef !== null && $invoiceNumberRef > 0 && $invoiceTotal !== null && Schema::hasColumn('payment_details', 'invoice_number')) {
                    $paidSum = (float) DB::table('payment_details')
                        ->where('invoice_number', $invoiceNumberRef)
                        ->where('status', 'Paid')
                        ->sum('subtotal_order');

                    $remaining = max($invoiceTotal - $paidSum, 0);
                    if ($amount > $remaining + 0.00001) {
                        throw ValidationException::withMessages([
                            'amount' => ["Amount exceeds invoice remaining. Invoice #{$invoiceNumberRef} total={$invoiceTotal}, paid={$paidSum}, remaining={$remaining}"]
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Best-effort guard: skip enforcement if invoice table/columns are unavailable                
            }
        }

        // Optional columns detection
        $methodCol   = Schema::hasColumn('payment_details', 'pmethod') ? 'pmethod'
                     : (Schema::hasColumn('payment_details', 'payment_method') ? 'payment_method' : null);
        $dateCol     = Schema::hasColumn('payment_details', 'paid_at') ? 'paid_at'
                     : (Schema::hasColumn('payment_details', 'date') ? 'date'
                     : (Schema::hasColumn('payment_details', 'created_at') ? 'created_at' : null));
        $remarksCol  = Schema::hasColumn('payment_details', 'remarks') ? 'remarks' : null;
        $studNumCol  = Schema::hasColumn('payment_details', 'student_number') ? 'student_number' : null;
        $studCampCol = Schema::hasColumn('payment_details', 'student_campus') ? 'student_campus' : null;
        // Some environments require name/email columns without defaults
        $firstNameCol  = Schema::hasColumn('payment_details', 'first_name') ? 'first_name' : null;
        $middleNameCol = Schema::hasColumn('payment_details', 'middle_name') ? 'middle_name' : null;
        $lastNameCol   = Schema::hasColumn('payment_details', 'last_name') ? 'last_name' : null;
        $emailCol      = Schema::hasColumn('payment_details', 'email_address') ? 'email_address' : null;
        $contactCol      = Schema::hasColumn('payment_details', 'contact_number') ? 'contact_number' : null;
        // Support legacy or_date column; always stored as Y-m-d
        $orDateCol       = Schema::hasColumn('payment_details', 'or_date') ? 'or_date' : null;

        // Resolve optional student fields
        $studentNumber = null;
        $firstName = $middleName = $lastName = $email = $mobile = null;
        if ($studNumCol || $firstNameCol || $middleNameCol || $lastNameCol || $emailCol) {
            $usr = DB::table('tb_mas_users')
                ->select('strStudentNumber', 'strFirstname', 'strMiddlename', 'strLastname', 'strEmail')
                ->where('intID', $studentId)
                ->first();
            if ($usr) {
                if (isset($usr->strStudentNumber)) $studentNumber = (string) $usr->strStudentNumber;
                if (isset($usr->strFirstname)) $firstName = (string) $usr->strFirstname;
                if (isset($usr->strMiddlename)) $middleName = (string) $usr->strMiddlename;
                if (isset($usr->strLastname)) $lastName = (string) $usr->strLastname;
                if (isset($usr->strEmail)) $email = (string) $usr->strEmail;
                if (isset($usr->strMobileNumber)) $mobile = (string) $usr->strMobileNumber;
            }
        }
                
        // Build insert payload
        $insert = [
            'student_information_id' => $studentId,
            'sy_reference'           => $syid, // SYID
            'description'            => $desc,
            'subtotal_order'         => $amount,
            'total_amount_due'       => $amount + $conFee,
            'status'                 => 'Paid',
            'convenience_fee'        => $conFee,
            'request_id'             => $requestId,
            'slug'                   => '',
        ];
        // number column handling
        if ($mode === 'none') {
            // no primary number assigned at creation time
        } else {
            if ($skipOrAndUseInvoice) {
                $insert[$numberCol] = (int) $invoiceNumberRef;
            } else {
                $insert[$numberCol] = (int) $current;
            }
        }
        if ($methodCol && $methodIn !== null) {
            $insert[$methodCol] = $methodIn;
        }
        if ($remarksCol) {
            $insert[$remarksCol] = $remarks;
        }
        if ($studNumCol) {
            $insert[$studNumCol] = $studentNumber !== null ? $studentNumber : '';
        }
        if ($studCampCol && $campusId !== null) {
            $insert[$studCampCol] = $campusId;
        }
        // Fill name/email columns if present (provide empty strings when unknown to satisfy NOT NULL constraints)
        if ($firstNameCol)  $insert[$firstNameCol]  = $firstName  !== null ? $firstName  : '';
        if ($middleNameCol) $insert[$middleNameCol] = $middleName !== null ? $middleName : '';
        if ($lastNameCol)   $insert[$lastNameCol]   = $lastName   !== null ? $lastName   : '';
        if ($emailCol)      $insert[$emailCol]      = $email      !== null ? $email      : '';
        if ($contactCol)    $insert[$contactCol]    = $mobile     !== null ? $mobile      : '';
        if ($dateCol) {
            $insert[$dateCol] = $postedAt ?: date('Y-m-d H:i:s');
        }
        // Default or_date (date-only) to today when column exists
        if ($orDateCol) {
            $orDate = null;
            if (isset($payload['or_date'])) {
                $s = (string) $payload['or_date'];
                // Normalize to date-only (Y-m-d) even if time is provided
                $orDate = substr($s, 0, 10);
            }
            $insert[$orDateCol] = $orDate ?: date('Y-m-d');
        }
        // Persist selected mode_of_payment_id when column exists
        if (Schema::hasColumn('payment_details', 'mode_of_payment_id') && $modePaymentId !== null) {
            $insert['mode_of_payment_id'] = $modePaymentId;
        }

        // When issuing an OR (mode='or') and an invoice is selected from the optional invoices,
        // persist the selected invoice's number into payment_details.invoice_number alongside the OR number.
        // Skip if we already stored invoice_number as the primary number for first payment.
        if (!$skipOrAndUseInvoice && $mode === 'or' && Schema::hasColumn('payment_details', 'invoice_number')) {
            try {
                $invoiceRow = null;
                $invoiceNumberRef = null;

                // Resolve via invoice_id when provided
                if (isset($payload['invoice_id'])) {
                    $iid = (int) $payload['invoice_id'];
                    if ($iid > 0 && Schema::hasTable('tb_mas_invoices')) {
                        $invoiceRow = DB::table('tb_mas_invoices')
                            ->select('intID', 'invoice_number')
                            ->where('intID', $iid)
                            ->first();
                    }
                }

                // Prefer number from resolved row; fallback to payload
                if ($invoiceRow && isset($invoiceRow->invoice_number) && $invoiceRow->invoice_number !== null && $invoiceRow->invoice_number !== '') {
                    $invoiceNumberRef = (int) $invoiceRow->invoice_number;
                }
                if ($invoiceNumberRef === null && isset($payload['invoice_number']) && $payload['invoice_number'] !== '') {
                    $invoiceNumberRef = (int) $payload['invoice_number'];
                }

                if ($invoiceNumberRef !== null && $invoiceNumberRef > 0) {
                    $insert['invoice_number'] = $invoiceNumberRef;
                }
            } catch (\Throwable $e) {
                // ignore linking if invoices table/column are unavailable
                echo "An error occurred: " . $e->getMessage();
            }
        }

        // Transaction: insert payment row and increment pointer
        $idInserted = null;        
        DB::transaction(function () use (&$idInserted, $insert, $row, $mode, $skipOrAndUseInvoice) {
            $idInserted = DB::table('payment_details')->insertGetId($insert);

            if ($mode === 'invoice') {
                $row->invoice_current = (int) $row->invoice_current + 1;
            } else {
                // Only advance OR pointer if we actually used an OR number
                if (!$skipOrAndUseInvoice) {
                    $row->or_current = (int) $row->or_current + 1;
                }
            }
            $row->save();
        });

        // System log: create payment detail
        try {
            $normalized = (new PaymentDetailAdminService())->getById((int) $idInserted);
        } catch (\Throwable $e) {
            $normalized = null;
        }
        SystemLogService::log('create', 'PaymentDetail', (int) $idInserted, null, $normalized ?? $insert, $request);

        // Post-payment hook: update applicant_data payment flags based on description
        try {
            if (Schema::hasTable('tb_mas_applicant_data')) {
                $descNorm = strtolower(trim((string) $desc));
                $isApp = in_array($descNorm, ['application payment','application fee'], true) || str_contains($descNorm, 'application');
                $isRes = in_array($descNorm, ['reservation payment'], true) || str_contains($descNorm, 'reservation');
                $isTuition = in_array($descNorm, ['tuition payment','tuition fee'], true) || str_contains($descNorm, 'tuition');
                $tuitionFlag = false;

                $updates = [];
                $applicantData = DB::table('tb_mas_applicant_data')
                ->where('user_id', $studentId)
                ->where('syid', $syid)
                ->first();
                if ($isApp && Schema::hasColumn('tb_mas_applicant_data','paid_application_fee')) {
                    $updates['paid_application_fee'] = 1;
                }
                if ($isRes && Schema::hasColumn('tb_mas_applicant_data','paid_reservation_fee')) {
                    $updates['paid_reservation_fee'] = 1;                    
                    $usrUpdates['student_status'] = "active";                      
                    //Set User to active                   
                    if($applicantData && $applicantData->status != "Enrolled"){
                        $usrUpdates['strStudentNumber'] = "T".date("Y").$applicantData->id;
                        $updates['status'] = "Reserved";
                    }
                    DB::table('tb_mas_users')
                    ->where('intID', $studentId)                    
                    ->update($usrUpdates);
                }
                if($isTuition){
                    
                    $regData = DB::table('tb_mas_registration')
                        ->where('intStudentID', $studentId)
                        ->where('intAYID', $syid)
                        ->first();

                    if($regData->enrollment_status != "enrolled"){
                        $regUpdates['date_enrolled'] = date("Y-m-d H:i:s");
                        $regUpdates['enrollment_status'] = "enrolled";                        
                        $tuitionFlag = true;
                        DB::table('tb_mas_registration')
                                ->where('intStudentID', $studentId)                    
                                ->update($regUpdates);
                        if($applicantData && $applicantData->status != "Enrolled"){
                            $updates['status'] = "Enrolled";                            
                            // Assign Student Number: generate final number upon enrollment flip
                            try {
                                $finalStudentNumber = $this->generateStudentNumber($syid);
                                if (!empty($finalStudentNumber)) {
                                    $userBefore = DB::table('tb_mas_users')->select('intID','strStudentNumber')->where('intID', $studentId)->first();
                                    DB::table('tb_mas_users')
                                        ->where('intID', $studentId)
                                        ->update(['strStudentNumber' => $finalStudentNumber]);
                                    try {
                                        SystemLogService::log(
                                            'assign_student_number',
                                            'User',
                                            (int) $studentId,
                                            ['strStudentNumber' => $userBefore->strStudentNumber ?? null],
                                            ['strStudentNumber' => $finalStudentNumber],
                                            $request
                                        );
                                    } catch (\Throwable $e4) {
                                        // ignore log failures
                                    }
                                }
                            } catch (\Throwable $eSN) {
                                // silently skip if generation fails; do not block payment creation
                            }
                        }
                    }
                }

                if (!empty($updates)) {
                    $updates['updated_at'] = now();

                    $affected = 0;
                    if (Schema::hasColumn('tb_mas_applicant_data','syid') && $syid > 0) {
                        $affected = DB::table('tb_mas_applicant_data')
                            ->where('user_id', $studentId)
                            ->where('syid', $syid)
                            ->update($updates);
                    }

                    if ($affected === 0) {
                        $latest = DB::table('tb_mas_applicant_data')
                            ->where('user_id', $studentId)
                            ->orderByDesc('id')
                            ->first();
                        if ($latest && isset($latest->id)) {
                            DB::table('tb_mas_applicant_data')
                                ->where('id', $latest->id)
                                ->update($updates);
                        }
                    }

                    // Non-blocking system log for applicant flags update
                    try {
                        SystemLogService::log(
                            'update',
                            'ApplicantPaymentFlag',
                            (int) $studentId,
                            null,
                            array_merge(['syid' => $syid], $updates),
                            $request
                        );
                    } catch (\Throwable $e2) {
                        // ignore log failure
                    }

                    // Send email notifications for payment milestones
                    try {
                        $phpMailerService = app(\App\Services\PHPMailerService::class);
                        
                        // Get applicant information for emails
                        $userInfo = DB::table('tb_mas_users')->where('intID', $studentId)->first();
                        $applicantInfo = DB::table('tb_mas_applicant_data')
                            ->where('user_id', $studentId)
                            ->where('syid', $syid)
                            ->first();
                        
                        if ($userInfo && $applicantInfo) {
                            $applicantName = trim(($userInfo->strFirstname ?? '') . ' ' . ($userInfo->strLastname ?? ''));
                            $applicationNumber = 'A' . str_pad($applicantInfo->id, 6, '0', STR_PAD_LEFT);
                            
                            // Notify admissions about application fee payment
                            if (isset($updates['paid_application_fee'])) {
                                $phpMailerService->sendAdmissionsApplicationFeePaymentNotification([
                                    'applicant_name' => $applicantName,
                                    'applicant_email' => $userInfo->strEmail ?? '',
                                    'application_number' => $applicationNumber,
                                    'payment_amount' => $amount + $conFee,
                                    'payment_date' => now()->format('Y-m-d H:i:s'),
                                    'payment_reference' => $requestId ?? 'N/A'
                                ]);
                            }
                            
                            // Notify admissions about reservation fee payment
                            if (isset($updates['paid_reservation_fee'])) {
                                $phpMailerService->sendAdmissionsReservationFeePaymentNotification([
                                    'applicant_name' => $applicantName,
                                    'applicant_email' => $userInfo->strEmail ?? '',
                                    'application_number' => $applicationNumber,
                                    'payment_amount' => $amount + $conFee,
                                    'payment_date' => now()->format('Y-m-d H:i:s'),
                                    'payment_reference' => $requestId ?? 'N/A'
                                ]);
                            }
                            
                            // Notify registrar when applicant status changes to Reserved
                            if (isset($updates['status']) && $updates['status'] === 'Reserved') {
                                $phpMailerService->sendRegistrarReservedNotification([
                                    'applicant_name' => $applicantName,
                                    'applicant_email' => $userInfo->strEmail ?? '',
                                    'application_number' => $applicationNumber,
                                    'status_change_date' => now()->format('Y-m-d H:i:s'),
                                    'previous_status' => $applicantData->status ?? 'Unknown'
                                ]);
                            }
                            
                            // Notify admissions when applicant status changes to Enrolled
                            if (isset($updates['status']) && $updates['status'] === 'Enrolled') {
                                $phpMailerService->sendAdmissionsApplicantEnrolledNotification([
                                    'applicant_name' => $applicantName,
                                    'applicant_email' => $userInfo->strEmail ?? '',
                                    'application_number' => $applicationNumber,
                                    'enrollment_date' => now()->format('Y-m-d H:i:s'),
                                    'previous_status' => $applicantData->status ?? 'Unknown'
                                ]);
                            }
                        }
                    } catch (\Throwable $e3) {
                        // Don't block payment processing if email fails
                        \Illuminate\Support\Facades\Log::warning('Payment notification email failed: ' . $e3->getMessage());
                    }

                    // Journey logs: payment-related milestones
                    try {
                        // Resolve applicant_data row to attach logs
                        $target = DB::table('tb_mas_applicant_data')->where('user_id', $studentId);
                        if (Schema::hasColumn('tb_mas_applicant_data','syid') && $syid > 0) {
                            $target = $target->where('syid', $syid);
                        }
                        $appRow = $target->orderByDesc('id')->first();
                        if (!$appRow) {
                            $appRow = DB::table('tb_mas_applicant_data')
                                ->where('user_id', $studentId)
                                ->orderByDesc('id')
                                ->first();
                        }

                        if ($appRow && isset($appRow->id)) {
                            if ($isApp) {
                                $alertMessage = 'Student paid application fee';
                                app(\App\Services\ApplicantJourneyService::class)
                                    ->log((int) $appRow->id, 'Student paid application fee');
                            }
                            if ($isRes && isset($updates['status']) && $updates['status'] === 'Reserved') {
                                $alertMessage = 'Reservation Fee paid! Status was changed to Reserved';
                                app(\App\Services\ApplicantJourneyService::class)
                                    ->log((int) $appRow->id, 'Status was changed to Reserved');
                            }
                            if ($tuitionFlag && isset($updates['status']) && $updates['status'] === 'Enrolled' && $applicantData && $applicantData->status != "Enrolled"){
                                $alertMessage = 'Status was changed to Enrolled';
                                app(\App\Services\ApplicantJourneyService::class)
                                    ->log((int) $appRow->id, 'Status was changed to Enrolled');
                            }
                            if($applicantData && $applicantData->status != "Enrolled"){
                                // System alert: notify Admissions about new application
                                try {
                                    $last = strtoupper(trim((string) $usr->strLastname));
                                    $first = trim((string) $usr->strFirstname);
                                    $emailAddr = trim((string) $usr->strEmail);
                                    $subject = $alertMessage;
                                    $namePart = trim(($last !== '' ? $last : '') . ($first !== '' ? ($last !== '' ? ', ' : '') . $first : ''));
                                    $msg = 'Applicant Update' . ($namePart !== '' ? (': ' . $namePart) : '') . ($emailAddr !== '' ? ' (' . $emailAddr . ')' : '');                                

                                    $payloadAlert = [
                                        'title'            => $subject,
                                        'message'          => $msg,
                                        'link'             => '#/admissions/applicants/' . $studentId,
                                        'type'             => 'info',
                                        'target_all'       => false,
                                        'role_codes'       => ['admissions','registrar'],
                                        'intActive'        => 1,
                                        'system_generated' => 1,
                                        'starts_at'        => now(),
                                    ];
                                    if ($campusId !== null && $campusId !== '' && is_numeric($campusId)) {
                                        $payloadAlert['campus_ids'] = [ (int) $campusId ];
                                    }

                                    $alert = \App\Models\SystemAlert::create($payloadAlert);
                                    app(\App\Services\SystemAlertService::class)->broadcast('create', $alert);
                                } catch (\Throwable $e) {
                                    print_r($e->getMessage());
                                }
                            }
                        }
                    } catch (\Throwable $e3) {
                        // ignore journey logging failures
                    }
                }
            }
        } catch (\Throwable $e) {
            // do not interrupt payment creation on applicant update issues
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => (int) $idInserted,
                'number_used' => $skipOrAndUseInvoice ? (int) $invoiceNumberRef : (int) $current,
                'mode'        => $mode,
                'cashier_id'  => (int) $row->intID,
            ],
        ], 201);
    }

    /**
     * Generate a final student number for the given term (syid).
     * Format (no separators):
     *  - College: {strYearStart}{semCode}{NNN} e.g., 202501001
     *  - SHS:     {strYearStart}SHA{semCode}{NNN} e.g., 2025SHA01001
     *
     * Rules:
     *  - semCode: enumSem "1st"->"01", "2nd"->"02", "3rd"->"03", "4th"->"04"
     *             fallback extracts digits and left-pads to 2
     *  - Counter: incrementing 3-digit suffix per unique prefix, based on max existing
     */
    protected function generateStudentNumber(int $syid): string
    {
        $sy = DB::table('tb_mas_sy')
            ->select('strYearStart', 'enumSem', 'term_student_type')
            ->where('intID', $syid)
            ->first();

        if (!$sy) {
            throw new \RuntimeException('Term (tb_mas_sy) not found for syid=' . $syid);
        }

        $year = isset($sy->strYearStart) ? (string) $sy->strYearStart : '';
        $sem  = isset($sy->enumSem) ? (string) $sy->enumSem : '';
        $type = isset($sy->term_student_type) ? (string) $sy->term_student_type : '';

        // Map enumSem to two-digit code
        $map = [
            '1st' => '01',
            '2nd' => '02',
            '3rd' => '03',
            '4th' => '04',
        ];
        $semKey = strtolower(trim($sem));
        $semCode = $map[$semKey] ?? null;
        if ($semCode === null) {
            if (preg_match('/\d+/', $semKey, $m)) {
                $semCode = str_pad((string) $m[0], 2, '0', STR_PAD_LEFT);
            } else {
                $semCode = '01'; // safe default
            }
        }

        // SHS detection - insert "SHA" after year
        $isShs = (stripos($type, 'shs') !== false);
        $basePrefix = $year . ($isShs ? 'SHA' : '') . $semCode;

        // Campus letter prefix: when campus_id != 1, prepend first letter of campus name
        $fullPrefix = $basePrefix;
        try {
            if (Schema::hasTable('tb_mas_sy') && Schema::hasColumn('tb_mas_sy', 'campus_id')) {
                $campusIdVal = DB::table('tb_mas_sy')->where('intID', $syid)->value('campus_id');
                if (!is_null($campusIdVal) && (int) $campusIdVal !== 1 && Schema::hasTable('tb_mas_campuses')) {
                    $campusName = DB::table('tb_mas_campuses')->where('id', (int) $campusIdVal)->value('campus_name');
                    if (is_string($campusName) && trim($campusName) !== '') {
                        $letter = strtoupper(substr(trim($campusName), 0, 1));
                        $fullPrefix = $letter . $basePrefix;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore campus prefixing failures; fallback to base prefix
        }

        // Find current max for this prefix
        $latest = DB::table('tb_mas_users')
            ->whereNotNull('strStudentNumber')
            ->where('strStudentNumber', 'like', $fullPrefix . '%')
            ->orderBy('strStudentNumber', 'desc')
            ->value('strStudentNumber');

        $next = 1;
        if (!empty($latest) && is_string($latest)) {
            $suffix = substr($latest, strlen($fullPrefix));
            if ($suffix !== false) {
                $digits = preg_replace('/\D/', '', $suffix);
                if ($digits !== '' && ctype_digit($digits)) {
                    $next = max(1, (int) $digits + 1);
                }
            }
        }

        // Collision hardening: recheck existence and increment until available
        for ($attempts = 0; $attempts < 5; $attempts++) {
            $candidate = $fullPrefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $exists = DB::table('tb_mas_users')->where('strStudentNumber', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $next++;
        }

        // Fallback last resort
        return $fullPrefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
