<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UnityAdvisingRequest;
use App\Http\Requests\Api\V1\UnityEnlistRequest;
use App\Http\Requests\Api\V1\UnityResetRegistrationRequest;
use App\Http\Requests\Api\V1\UnityRegistrationUpdateRequest;
use App\Http\Resources\TuitionBreakdownResource;
use App\Services\TuitionService;
use App\Services\EnlistmentService;
use App\Services\UserContextResolver;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cashier;

class UnityController extends Controller
{
    protected TuitionService $tuition;
    protected EnlistmentService $enlistment;
    protected UserContextResolver $ctx;
    protected RegistrationService $registration;

    public function __construct(TuitionService $tuition, EnlistmentService $enlistment, UserContextResolver $ctx, RegistrationService $registration)
    {
        $this->tuition = $tuition;
        $this->enlistment = $enlistment;
        $this->ctx = $ctx;
        $this->registration = $registration;
    }

    /**
     * POST /api/v1/unity/advising
     * Body: UnityAdvisingRequest
     * Returns a placeholder advising plan echoing input for now.
     */
    public function advising(UnityAdvisingRequest $request): JsonResponse
    {
        $payload = $request->validated();

        // Placeholder: echo subjects back as the "plan"
        $plan = [
            'student_number' => $payload['student_number'],
            'program_id'     => $payload['program_id'],
            'term'           => $payload['term'],
            'subjects'       => $payload['subjects'],
            'notes'          => 'Advising logic not yet implemented. This is a placeholder response.',
        ];

        return response()->json([
            'success' => true,
            'data'    => $plan,
        ]);
    }

    /**
     * POST /api/v1/unity/enlist
     * Executes registrar enlistment operations (add/drop/change_section) for a student and term.
     */
    public function enlist(UnityEnlistRequest $request): JsonResponse
    {
        $result = $this->enlistment->enlist($request->validated(), $request);
        $status = ($result['success'] ?? false) ? 200 : 400;
        return response()->json($result, $status);
    }

    /**
     * POST /api/v1/unity/reset-registration
     * Body: UnityResetRegistrationRequest
     * Deletes tb_mas_classlist_student rows and the tb_mas_registration row(s) for a student/term.
     * If term is omitted, defaults to the active term (latest by year/sem).
     */
    public function resetRegistration(UnityResetRegistrationRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $studentNumber = $payload['student_number'];
        $term = $payload['term'] ?? null;
        $password = $payload['password'] ?? '';

        // Resolve student by student number
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }
        $studentId = (int) $user->intID;

        // Resolve term if omitted: pick active term (latest by year then sem)
        if ($term === null) {
            $activeTerm = DB::table('tb_mas_sy')
                ->orderBy('strYearStart', 'desc')
                ->orderBy('enumSem', 'asc')
                ->first();
            if (!$activeTerm) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active term available',
                ], 422);
            }
            $term = (int) $activeTerm->intID;
        } else {
            $term = (int) $term;
        }

        // Resolve acting user (registrar/admin) and validate password
        $actorId = $this->ctx->resolveUserId($request);
        if ($actorId === null) {
            $xfac = $request->header('X-Faculty-ID');
            if ($xfac !== null && is_numeric($xfac)) {
                $actorId = (int) $xfac;
            }
        }
        if ($actorId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: could not resolve acting user',
            ], 401);
        }

        $fac = DB::table('tb_mas_faculty')->where('intID', $actorId)->first();
        if (!$fac || !isset($fac->strPass)) {
            return response()->json([
                'success' => false,
                'message' => 'Password confirmation failed',
            ], 403);
        }

        $ok = false;
        $stored = (string) $fac->strPass;
        if (password_verify($password, $stored)) {
            $ok = true;
        } else {
            $decoded = $this->legacyUnhash($stored);
            if ($decoded !== null && hash_equals($decoded, $password)) {
                $ok = true;
            }
        }
        if (!$ok) {
            return response()->json([
                'success' => false,
                'message' => 'Password confirmation failed',
            ], 403);
        }

        // Proceed with reset
        $result = $this->enlistment->resetRegistration($studentId, $term, $request);
        $status = ($result['success'] ?? false) ? 200 : 400;
        return response()->json($result, $status);
    }

    /**
     * Legacy CI pw_hash unhash equivalent used to support old stored passwords.
     * Mirrors application/libraries/Salting::unhash_string behavior.
     */
    protected function legacyUnhash(?string $hashed): ?string
    {
        if (!$hashed || strlen($hashed) < 10) {
            return null;
        }
        // Remove first 5 and last 5 chars (prefix/suffix)
        $core = substr($hashed, 5, -5);
        $len = strlen($core);
        $get = '';
        $inc = 0;
        for ($i = 0; $i < $len; $i++) {
            if ($inc === 0) {
                $inc = 1;
                $get .= $core[$i];
            } else {
                $inc = 0;
            }
        }
        return strrev($get);
    }

    /**
     * GET /api/v1/unity/registration
     * Query: student_number, term
     * Returns the existing registration for a student/term, or exists=false when absent.
     */
    public function registration(Request $request): JsonResponse
    {
        // Accept either student_id (preferred) or student_number (legacy)
        $validated = $request->validate([
            'student_id'     => 'nullable|integer',
            'student_number' => 'nullable|string',
            'term'           => 'required|integer',
        ]);

        $term = (int) $validated['term'];
        $studentId = $validated['student_id'] ?? null;
        $studentNumber = $validated['student_number'] ?? null;

        if (($studentId === null || $studentId === '') && ($studentNumber === null || $studentNumber === '')) {
            return response()->json([
                'success' => false,
                'message' => 'student_id or student_number is required',
            ], 422);
        }

        if ($studentId !== null && $studentId !== '') {
            $sid = (int) $studentId;
            if ($sid <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid student_id',
                ], 422);
            }
            // Prefer lookup by student_id when provided
            if (method_exists($this->registration, 'findByStudentIdAndTerm')) {
                $row = $this->registration->findByStudentIdAndTerm($sid, $term);
            } else {
                // Fallback: resolve number then delegate (should not happen if service updated)
                $user = DB::table('tb_mas_users')->where('intID', $sid)->first();
                if (!$user) {
                    $row = null;
                } else {
                    $row = $this->registration->findByStudentNumberAndTerm((string) $user->strStudentNumber, $term);
                }
            }
        } else {
            $row = $this->registration->findByStudentNumberAndTerm((string) $studentNumber, $term);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exists' => $row !== null,
                'registration' => $row,
            ],
        ]);
    }

    /**
     * PUT /api/v1/unity/registration
     * Body: UnityRegistrationUpdateRequest
     * Strictly edits only when a registration exists for the student/term. No creation.
     * Audit-logs via SystemLogService.
     */
    public function updateRegistration(UnityRegistrationUpdateRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = $this->registration->updateByStudentNumberAndTerm(
            (string) $payload['student_number'],
            (int) $payload['term'],
            (array) ($payload['fields'] ?? []),
            $request
        );

        $status = $result['status'] ?? (($result['success'] ?? false) ? 200 : 400);
        unset($result['status']);

        return response()->json($result, $status);
    }

    /**
     * POST /api/v1/unity/tag-status
     * Placeholder endpoint - not yet implemented.
     */
    public function tagStatus(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tag status not implemented',
        ], 501);
    }

    /**
     * POST /api/v1/unity/tuition-preview
     * Body:
     *  - student_number: string
     *  - program_id: int
     *  - term: string (syid)
     *  - subjects: array of { subject_id: int, section?: string } (not used in compute; kept for compatibility)
     *  - discount_id?: int (optional)
     *  - scholarship_id?: int (optional)
     * Returns TuitionBreakdownResource using full computation.
     */
    public function tuitionPreview(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number'        => 'required|string',
            'program_id'            => 'required|integer',
            'term'                  => 'required|string',
            'subjects'              => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|integer',
            'subjects.*.section'    => 'nullable|string',
            'discount_id'           => 'sometimes|nullable|integer',
            'scholarship_id'        => 'sometimes|nullable|integer',
        ]);

        try {
            // Parse term to integer SYID (frontend sends selectedTerm.intID as string)
            $syid = (int) $payload['term'];

            $discountId = $payload['discount_id'] ?? null;
            $scholarshipId = $payload['scholarship_id'] ?? null;

            // Use full compute path based on existing registration + tuition_year
            $breakdown = $this->tuition->compute(
                (string) $payload['student_number'],
                $syid,
                $discountId !== null ? (int) $discountId : null,
                $scholarshipId !== null ? (int) $scholarshipId : null
            );

            return response()->json([
                'success' => true,
                'data'    => new TuitionBreakdownResource($breakdown),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compute tuition preview',
            ], 500);
        }
    }

    /**
     * POST /api/v1/unity/tuition-save
     * Body:
     *  - student_number: string
     *  - term: integer (syid)
     *  - discount_id?: int (optional)
     *  - scholarship_id?: int (optional)
     * Behavior:
     *  - Resolve student and registration (must exist and have tuition_year).
     *  - Recompute tuition using TuitionService->compute.
     *  - Upsert into tb_mas_tuition_saved keyed by (intStudentID,intRegistrationID); overwrite if exists.
     */
    public function tuitionSave(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'student_number' => 'required|string',
            'term'           => 'required|integer',
            'discount_id'    => 'sometimes|nullable|integer',
            'scholarship_id' => 'sometimes|nullable|integer',
        ]);

        $studentNumber = (string) $payload['student_number'];
        $syid = (int) $payload['term'];
        $discountId = $payload['discount_id'] ?? null;
        $scholarshipId = $payload['scholarship_id'] ?? null;

        // Resolve student and registration
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }
        $registration = DB::table('tb_mas_registration')
            ->where('intStudentID', $user->intID)
            ->where('intAYID', $syid)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found for term',
            ], 422);
        }
        if (!($registration->tuition_year ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Registration missing tuition_year',
            ], 422);
        }

        // Resolve acting user (saved_by)
        $actorId = $this->ctx->resolveUserId($request);
        if ($actorId === null) {
            $xfac = $request->header('X-Faculty-ID');
            if ($xfac !== null && is_numeric($xfac)) {
                $actorId = (int) $xfac;
            }
        }

        try {
            // Recompute server-side
            $breakdown = $this->tuition->compute(
                $studentNumber,
                $syid,
                $discountId !== null ? (int) $discountId : null,
                $scholarshipId !== null ? (int) $scholarshipId : null
            );

            $now = now()->toDateTimeString();
            $key = [
                'intStudentID'     => (int) $user->intID,
                'intRegistrationID'=> (int) $registration->intRegistrationID,
            ];

            $existing = DB::table('tb_mas_tuition_saved')
                ->where($key)
                ->first();

            if ($existing) {
                DB::table('tb_mas_tuition_saved')
                    ->where($key)
                    ->update([
                        'syid'       => $syid,
                        'payload'    => json_encode($breakdown),
                        'saved_by'   => $actorId,
                        'updated_at' => $now,
                    ]);
                $savedId = (int) $existing->intID;
                $overwritten = true;
            } else {
                $savedId = DB::table('tb_mas_tuition_saved')->insertGetId([
                    'intStudentID'      => (int) $user->intID,
                    'intRegistrationID' => (int) $registration->intRegistrationID,
                    'syid'              => $syid,
                    'payload'           => json_encode($breakdown),
                    'saved_by'          => $actorId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $overwritten = false;
            }

            // Generate or update tuition invoice linked to this registration.
            // Amount is the computed total_due from tuition breakdown.
            try {
                // Determine invoice amount based on registered payment type:
                // - 'partial' => use installments.total_installment when available; fallback to total_due
                // - others    => use total_due; fallback to installments.total_installment
                $sum = is_array($breakdown['summary'] ?? null) ? $breakdown['summary'] : [];
                $installments = is_array($sum['installments'] ?? null) ? $sum['installments'] : [];
                $instTotal = $installments['total_installment'] ?? ($sum['total_installment'] ?? null);
                $totalDue = $sum['total_due'] ?? null;
                $pt = isset($registration->paymentType) ? strtolower((string) $registration->paymentType) : null;
                $amount = null;
                if ($pt === 'partial') {
                    $amount = is_numeric($instTotal) ? (float) $instTotal : (is_numeric($totalDue) ? (float) $totalDue : 0.0);
                } else {
                    $amount = is_numeric($totalDue) ? (float) $totalDue : (is_numeric($instTotal) ? (float) $instTotal : 0.0);
                }
                if (is_finite($amount)) {
                    // Resolve acting cashier (by faculty/actor id)
                    $cashier = $actorId ? Cashier::query()->where('faculty_id', (int)$actorId)->first() : null;

                    // Determine if a tuition invoice already exists for this registration
                    $existingInvoice = DB::table('tb_mas_invoices')
                        ->where('registration_id', (int) $registration->intRegistrationID)
                        ->where('type', 'tuition')
                        ->orderBy('intID', 'desc')
                        ->first();

                    // Build payload and options
                    $payload = [
                        'source' => 'tuition-save',
                        'meta' => [
                            'registration_id' => (int) $registration->intRegistrationID,
                            'syid'            => (int) $syid,
                            'total_due'       => $amount,
                        ],
                    ];

                    $options = [
                        'payload' => $payload,
                    ];

                    // Default campus_id precedence: request input -> X-Campus-ID header -> cashier campus
                    $reqCampus = $request->input('campus_id');
                    if ($reqCampus !== null && $reqCampus !== '' && is_numeric($reqCampus)) {
                        $options['campus_id'] = (int) $reqCampus;
                    } else {
                        $hdrCampus = $request->header('X-Campus-ID');
                        if ($hdrCampus !== null && $hdrCampus !== '' && is_numeric($hdrCampus)) {
                            $options['campus_id'] = (int) $hdrCampus;
                        }
                    }

                    // Use cashier campus if campus_id still not resolved
                    if ($cashier) {
                        if (!array_key_exists('campus_id', $options) || $options['campus_id'] === null || $options['campus_id'] === '') {
                            if (isset($cashier->campus_id)) {
                                $options['campus_id'] = (int) $cashier->campus_id;
                            }
                        }
                        $options['cashier_id'] = (int) $cashier->intID;
                    }

                    // Decide whether we will assign/consume a cashier invoice number
                    $willAssignInvoiceNo = false;
                    if ($cashier && !empty($cashier->invoice_current)) {
                        // For existing invoice: only assign if it has no number yet
                        if ($existingInvoice && empty($existingInvoice->invoice_number)) {
                            $options['invoice_number'] = (int) $cashier->invoice_current;
                            $willAssignInvoiceNo = true;
                        }
                        // For create: assign number when generating a new invoice
                        if (!$existingInvoice) {
                            $options['invoice_number'] = (int) $cashier->invoice_current;
                            $willAssignInvoiceNo = true;
                        }
                    }

                    // Update existing invoice amount regardless of cashier presence.
                    if ($existingInvoice) {
                        app(\App\Services\InvoiceService::class)->upsertTuitionByRegistration(
                            (int) $registration->intRegistrationID,
                            (int) $user->intID,
                            (int) $syid,
                            $amount,
                            $options,
                            $actorId
                        );

                        // Increment cashier's invoice_current if we assigned a number to an unnumbered invoice
                        if ($willAssignInvoiceNo && !empty($options['invoice_number'])) {
                            $cashier->invoice_current = (int) $cashier->invoice_current + 1;
                            $cashier->save();
                        }
                    } else {
                        // No existing invoice: only create when cashier context is available
                        if ($cashier) {
                            app(\App\Services\InvoiceService::class)->upsertTuitionByRegistration(
                                (int) $registration->intRegistrationID,
                                (int) $user->intID,
                                (int) $syid,
                                $amount,
                                $options,
                                $actorId
                            );

                            // Increment cashier pointer if we consumed one
                            if ($willAssignInvoiceNo && !empty($options['invoice_number'])) {
                                $cashier->invoice_current = (int) $cashier->invoice_current + 1;
                                $cashier->save();
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Do not block tuition save if invoice upsert fails
            }

            return response()->json([
                'success' => true,
                'message' => $overwritten ? 'Saved tuition overwritten successfully' : 'Saved tuition created successfully',
                'data'    => [
                    'id'               => (int) $savedId,
                    'intStudentID'     => (int) $user->intID,
                    'intRegistrationID'=> (int) $registration->intRegistrationID,
                    'syid'             => (int) $syid,
                    'saved_by'         => $actorId,
                    'overwritten'      => $overwritten,
                    'saved_at'         => $now,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save tuition snapshot',
            ], 500);
        }
    }

    /**
     * GET /api/v1/unity/tuition-saved
     * Query:
     *  - student_number: string
     *  - term: integer (syid)
     * Returns the saved tuition snapshot row if exists for the student's registration in the term.
     */
    public function tuitionSaved(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_number' => 'required|string',
            'term'           => 'required|integer',
        ]);

        $studentNumber = (string) $validated['student_number'];
        $syid = (int) $validated['term'];

        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            return response()->json([
                'success' => true,
                'data'    => ['exists' => false, 'saved' => null],
            ]);
        }

        $registration = DB::table('tb_mas_registration')
            ->where('intStudentID', $user->intID)
            ->where('intAYID', $syid)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => true,
                'data'    => ['exists' => false, 'saved' => null],
            ]);
        }

        $row = DB::table('tb_mas_tuition_saved')
            ->where('intStudentID', $user->intID)
            ->where('intRegistrationID', $registration->intRegistrationID)
            ->first();

        if (!$row) {
            return response()->json([
                'success' => true,
                'data'    => ['exists' => false, 'saved' => null],
            ]);
        }

        // Decode JSON payload to array for API response
        $saved = [
            'intID'            => (int) $row->intID,
            'intStudentID'     => (int) $row->intStudentID,
            'intRegistrationID'=> (int) $row->intRegistrationID,
            'syid'             => (int) $row->syid,
            'saved_by'         => $row->saved_by !== null ? (int) $row->saved_by : null,
            'created_at'       => (string) $row->created_at,
            'updated_at'       => (string) $row->updated_at,
            'payload'          => json_decode($row->payload, true),
        ];

        return response()->json([
            'success' => true,
            'data'    => ['exists' => true, 'saved' => $saved],
        ]);
    }
}
