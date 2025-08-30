<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CashierStoreRequest;
use App\Http\Requests\Api\V1\CashierUpdateRequest;
use App\Http\Requests\Api\V1\CashierRangeUpdateRequest;
use App\Http\Requests\Api\V1\CashierPaymentStoreRequest;
use App\Models\Cashier;
use App\Services\CashierService;
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
        $mode      = ($payload['mode'] === 'invoice') ? 'invoice' : 'or';
        $amount    = (float) $payload['amount'];
        $desc      = (string) $payload['description'];
        $remarks   = (string) $payload['remarks'];
        $methodIn  = isset($payload['method']) ? (string) $payload['method'] : null;
        $postedAt  = isset($payload['posted_at']) ? (string) $payload['posted_at'] : null;
        $campusId  = isset($payload['campus_id']) ? (int) $payload['campus_id'] : null;
        $modePaymentId = isset($payload['mode_of_payment_id']) ? (int) $payload['mode_of_payment_id'] : null;

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
        if ($numberCol === null) {
            throw ValidationException::withMessages([
                'mode' => ['Number column not available in payment_details for selected mode']
            ]);
        }

        // Determine pointers and range
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

        // Optional columns detection
        $methodCol   = Schema::hasColumn('payment_details', 'method') ? 'method'
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
            $numberCol               => (int) $current,
        ];
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
        // Persist selected mode_of_payment_id when column exists
        if (Schema::hasColumn('payment_details', 'mode_of_payment_id') && $modePaymentId !== null) {
            $insert['mode_of_payment_id'] = $modePaymentId;
        }

        // Transaction: insert payment row and increment pointer
        $idInserted = null;
        DB::transaction(function () use (&$idInserted, $insert, $row, $mode) {
            $idInserted = DB::table('payment_details')->insertGetId($insert);

            if ($mode === 'invoice') {
                $row->invoice_current = (int) $row->invoice_current + 1;
            } else {
                $row->or_current = (int) $row->or_current + 1;
            }
            $row->save();
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => (int) $idInserted,
                'number_used' => (int) $current,
                'mode'        => $mode,
                'cashier_id'  => (int) $row->intID,
            ],
        ], 201);
    }
}
