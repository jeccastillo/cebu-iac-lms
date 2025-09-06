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
use setasign\Fpdi\Fpdi;

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

    /**
     * GET /api/v1/unity/reg-form?student_number=SN&amp;term=SYID
     * Generates a student-specific Registration Form PDF by overlaying data on reg_form.pdf template.
     * Fields:
     *  - Student Number @ (16,78)
     *  - Student Name   @ (16,88)  format: Last, First Middle
     *  - Program        @ (16,98)  program code (registration.current_program fallback to user.intProgramID)
     *  - Term           @ (295,78) enumSem + YearStart-YearEnd
     *  - Address        @ (295,88)
     *  - Subjects start @ (16,128)  columns: Code, Description, Section, Units
     *  - Assessment     @ (16,288)  Tuition/Misc/Lab/Additional/Scholarships/Discounts/Total Due
     */
    public function regForm(Request $request)
    {
        $payload = $request->validate([
            'student_number' => 'required|string',
            'term'           => 'required|integer',
        ]);

        $sn = (string) $payload['student_number'];
        $syid = (int) $payload['term'];

        // Resolve student with fallback program code
        $user = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_programs as up', 'up.intProgramID', '=', 'u.intProgramID')
            ->select('u.*', 'up.strProgramCode as user_program_code')
            ->where('u.strStudentNumber', $sn)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // Registration row for the term + program code resolution
        $reg = DB::table('tb_mas_registration as r')
            ->leftJoin('tb_mas_programs as rp', 'rp.intProgramID', '=', 'r.current_program')
            ->select('r.*', 'rp.strProgramDescription as reg_program_code')
            ->where('r.intStudentID', (int) $user->intID)
            ->where('r.intAYID', $syid)
            ->first();

        $programCode = '';
        if ($reg && isset($reg->reg_program_code) && $reg->reg_program_code !== null && $reg->reg_program_code !== '') {
            $programCode = (string) $reg->reg_program_code;
        } elseif (isset($user->user_program_code) && $user->user_program_code !== null) {
            $programCode = (string) $user->user_program_code;
        }

        // Term label (enumSem + school year)
        $termRow = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        $termLabel = $termRow
            ? ((string) $termRow->enumSem . ' ' .(string)$termRow->term_label. ' ' .(string) $termRow->strYearStart . '-' . (string) $termRow->strYearEnd)
            : (string) $syid;

        // Student name: "Last, First Middle"
        $last = isset($user->strLastname) ? trim((string) $user->strLastname) : '';
        $first = isset($user->strFirstname) ? trim((string) $user->strFirstname) : '';
        $middle = isset($user->strMiddlename) ? trim((string) $user->strMiddlename) : '';
        $studentName = ($last !== '' ? ($last . ', ') : '') . $first . ($middle !== '' ? (' ' . $middle) : '');

        // Address
        $address = isset($user->strAddress) ? (string) $user->strAddress : '';

        // Current enlisted subjects for the term (include section)
        $subjects = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('cls.intStudentID', (int) $user->intID)
            ->where('cl.strAcademicYear', $syid)
            ->select(
                's.strCode as code',
                's.strDescription as description',
                's.strUnits as units',
                's.intLab as lab_units',
                DB::raw("COALESCE(cl.sectionCode, cl.strSection, '') as section_code")
            )
            ->orderBy('s.strCode', 'asc')
            ->get();

        // Tuition breakdown summary and items for assessment block
        $summary = null;
        $items = [];
        $installments = [];
        try {
            $breakdown = $this->tuition->compute($sn, $syid, null, null);
            $summary = is_array($breakdown['summary'] ?? null) ? $breakdown['summary'] : null;            
            $items = is_array($breakdown['items'] ?? null) ? $breakdown['items'] : [];
            $installments = is_array($summary['installments'] ?? null) ? $summary['installments'] : [];
        } catch (\Throwable $e) {
            $summary = null;
            $items = [];
            $installments = [];
        }

        // Build PDF (no external template). Use A3 size so existing coordinates fit.
        // This removes dependency on reg_form.pdf while preserving positions.
        $pdf = new \setasign\Fpdi\Fpdi('P', 'mm', array(8.5,13));
        $pdf->AddPage('P', 'A4');

        // Set text color and baseline font
        $pdf->SetTextColor(0, 0, 0);

        
        // Header fields (Letter page positioning)
        $pdf->SetFont('Helvetica', '', 8.5);
        
        $pdf->SetXY(5, 25);
        $pdf->Cell(0, 5, 'STUDENT NUMBER', 0, 1, 'L');

        
        $pdf->SetXY(46, 25);
        $pdf->Cell(0, 5, $sn, 0, 1, 'L');

        
        $pdf->SetXY(5, 30);
        $pdf->Cell(0, 5, 'NAME', 0, 1, 'L');
        // Student Name
        $pdf->SetXY(46, 30);
        $pdf->Cell(0, 5, strtoupper($studentName), 0, 1, 'L');

        
        $pdf->SetXY(5, 35);
        $pdf->Cell(0, 5, 'PROGRAM', 0, 1, 'L');
        // Program (code)
        $pdf->SetXY(46, 35);
        $pdf->MultiCell(60, 5, $programCode, 0,'L');

        $pdf->SetXY(115,25);
        $pdf->Cell(0, 5, 'TERM/SY', 0, 1, 'L');
        // Term (top-right for Letter)
        $pdf->SetXY(140, 25);
        $pdf->Cell(0, 5, $termLabel, 0, 1, 'L');

        $pdf->SetXY(115,30);
        $pdf->Cell(0, 5, 'ADDRESS', 0, 1, 'L');

        //Headers for subjects        
        $pdf->SetXY(140, 30);
        $pdf->MultiCell(60, 5, $address, 0, 'L');
        
        // Subjects table starting around (16,70): Code, Description, Section, Units (fitted to Letter width)
        
        $pdf->SetFont('Helvetica', 'B', 8.2);
        $lineH = 4;
        $columns = [5,25,105,120,130,145,170];
        // Code        
        $pdf->SetXY($columns[0], 55);
        $pdf->Cell(35, $lineH, "SECTION", 0, 0, 'L');
        // Description      
        $pdf->SetXY($columns[1], 55);  
        $pdf->Cell(95, $lineH, "SUBJECT NAME", 0, 0, 'L');
        // Section        
        $pdf->SetXY($columns[2], 55);
        $pdf->Cell(30, $lineH, "LAB", 0, 0, 'C');
        // Units        
        $pdf->SetXY($columns[3], 55);
        $pdf->Cell(20, $lineH, "UNITS", 0, 1, 'C');
        // DAY
        $pdf->SetXY($columns[4], 55);
        $pdf->Cell(20, $lineH, "DAY", 0, 1, 'C');
        // TIME
        $pdf->SetXY($columns[5], 55);
        $pdf->Cell(20, $lineH, "TIME", 0, 1, 'L');
        // ROOM        
        $pdf->SetXY($columns[6], 55);
        $pdf->Cell(20, $lineH, "ROOM", 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8.2);
        $y = 60;        
        foreach ($subjects as $subj) {
            $code = (string)($subj->code ?? '');
            $desc = (string)($subj->description ?? '');
            $sect = (string)($subj->section_code ?? '');
            $units = (string)($subj->units ?? '');
            $labUnits = (string)($subj->lab_units ?? '0');
            // Code
            $pdf->SetXY($columns[0], $y);
            $pdf->Cell(35, $lineH, $sect, 0, 0, 'L');
            // Description
            $pdf->SetXY($columns[1], $y);
            $pdf->Cell(95, $lineH, $desc, 0, 0, 'L');
            // Section
            $pdf->SetXY($columns[2], $y);
            $pdf->Cell(30, $lineH, $labUnits, 0, 0, 'C');
            // Units
            $pdf->SetXY($columns[3], $y);
            $pdf->Cell(20, $lineH, $units, 0, 1, 'C');

            $y += $lineH;
            // Stop early if we are approaching the footer area on Letter page
            if ($y > 100) {
                break;
            }
        }

        // Assessment block with three-column breakdown + schedules.
        if (is_array($summary)) {
            $money = function ($x) {
                return number_format((float)($x ?? 0), 2);
            };

            $items = is_array($breakdown['items'] ?? null) ? $breakdown['items'] : [];
            $miscItems = is_array($items['misc'] ?? null) ? $items['misc'] : [];
            $newStudentItems = is_array($items['new_student'] ?? null) ? $items['new_student'] : [];
            $newStudentTotal = 0.0;
            foreach ($newStudentItems as $r) {
                $newStudentTotal += (float)($r['amount'] ?? 0);
            }

            $inst = is_array($summary['installments'] ?? null) ? $summary['installments'] : [];

            // Column headers
            $colX = [5, 25, 55, 83, 113,153,163,188]; // FULL, 50%, 30%
            $colW = [20,30, 28, 30, 40, 10, 25, 10];
            // Layout anchors
            $pdf->SetFont('Helvetica', 'B', 8);
            $ay = 108;
            $pdf->SetXY($colX[0], $ay);
            $pdf->Cell(0, 5, 'ASSESSMENT SUMMARY', 0, 1, 'L');
            $pdf->SetXY($colX[4], $ay);
            $pdf->Cell(0, 5, 'MISCELLANEOUS DETAIL', 0, 1, 'L');
            $pdf->SetXY($colX[6], $ay);
            $pdf->Cell(0, 5, 'OTHER FEES DETAIL', 0, 1, 'L');
            $lineH = 3.5;
            // Misc list (left column)     
            $pdf->SetFont('Helvetica', '', 7);       
            $yL = $ay + 4.5;
            $yR = $ay + 4.7;
            $miscTotalNum = (float)($summary['misc_total'] ?? 0);
            if (!empty($miscItems)) {
                foreach ($miscItems as $row) {
                    $name = (string)($row['name'] ?? '');
                    $amt  = $money($row['amount'] ?? 0);
                    $pdf->SetXY($colX[4], $yL);
                    $pdf->Cell($colW[4], 4.5, $name, 0, 0, 'L');
                    $pdf->SetXY($colX[5], $yL);
                    $pdf->Cell($colW[5], 4.5, $amt, 0, 1, 'R');
                    $yL += $lineH;
                    //if ($yL > 270) { $pdf->AddPage('P', 'A4'); $yL = 20; }
                }
            }
            if (!empty($newStudentItems)) {
                foreach ($newStudentItems as $row) {
                    $name = (string)($row['name'] ?? '');
                    $amt  = $money($row['amount'] ?? 0);
                    $pdf->SetXY($colX[6], $yR);
                    $pdf->Cell($colW[6], $lineH, $name, 0, 0, 'L');
                    $pdf->SetXY($colX[7], $yR);
                    $pdf->Cell($colW[7], $lineH, $amt, 0, 1, 'R');
                    $yR += $lineH;
                    if ($yR > 270) { $pdf->AddPage('P', 'A4'); $yR = 20; }
                }
            }           
                        

            $pdf->SetFont('Helvetica', 'U', 7);
            $pdf->SetXY($colX[1], $ay + 5);
            $pdf->Cell($colW[1], $lineH, 'FULL PAYMENT', 0, 0, 'R');

            $pdf->SetXY($colX[2], $ay + 5);
            $pdf->Cell($colW[2], $lineH, '50% DOWN PAYMENT', 0, 0, 'R');

            $pdf->SetXY($colX[3], $ay + 5);
            $pdf->Cell($colW[3], $lineH, '30% DOWN PAYMENT', 0, 1, 'R');

            // Column rows
            $pdf->SetFont('Helvetica', '', 7);

            // ROW 1
            $y = $ay + 8;
            $pdf->SetXY($colX[0], $y);
            $pdf->Cell($colW[0], $lineH, 'Tuition Fee ', 0, 1, 'L');
            $pdf->SetXY($colX[1], $y);
            $pdf->Cell($colW[1], $lineH, $money($summary['tuition'] ?? 0), 0, 1, 'R');                  
            $pdf->SetXY($colX[2], $y);
            $pdf->Cell($colW[2], $lineH, $money(($summary['tuition'] + ($summary['tuition'] * 0.09)) ?? 0), 0, 1, 'R');
            $pdf->SetXY($colX[3], $y);
            $pdf->Cell($colW[3], $lineH, $money(($summary['tuition'] + ($summary['tuition'] * 0.15)) ?? 0), 0, 1, 'R');            
            // ROW 2
            $y += $lineH;
            $pdf->SetXY($colX[0], $y);
            $pdf->Cell($colW[0], $lineH, 'Laboratory ', 0, 1, 'L');
            $pdf->SetXY($colX[1], $y);
            $pdf->Cell($colW[1], $lineH, $money($summary['lab_total'] ?? 0), 0, 1, 'R');                  
            $pdf->SetXY($colX[2], $y);
            $pdf->Cell($colW[2], $lineH, $money(($summary['lab_total'] + ($summary['lab_total'] * 0.09)) ?? 0), 0, 1, 'R');
            $pdf->SetXY($colX[3], $y);
            $pdf->Cell($colW[3], $lineH, $money(($summary['lab_total'] + ($summary['lab_total'] * 0.15)) ?? 0), 0, 1, 'R');
            // ROW 3
            $y += $lineH;
            $pdf->SetXY($colX[0], $y);
            $pdf->Cell($colW[0], $lineH, 'Miscellaneous ', 0, 1, 'L');
            $pdf->SetXY($colX[1], $y);
            $pdf->Cell($colW[1], $lineH, $money($summary['misc_total'] ?? 0), 0, 1, 'R');                  
            $pdf->SetXY($colX[2], $y);
            $pdf->Cell($colW[2], $lineH, $money(($summary['misc_total']) ?? 0), 0, 1, 'R');
            $pdf->SetXY($colX[3], $y);
            $pdf->Cell($colW[3], $lineH, $money(($summary['misc_total'] ) ?? 0), 0, 1, 'R');
            // ROW 4
            if($newStudentTotal){
            $y += $lineH;
                $pdf->SetXY($colX[0], $y);
                $pdf->Cell($colW[0], $lineH, 'Other Fees ', 0, 1, 'L');
                $pdf->SetXY($colX[1], $y);
                $pdf->Cell($colW[1], $lineH, $money($newStudentTotal ?? 0), 0, 1, 'R');                  
                $pdf->SetXY($colX[2], $y);
                $pdf->Cell($colW[2], $lineH, $money(($newStudentTotal) ?? 0), 0, 1, 'R');
                $pdf->SetXY($colX[3], $y);
                $pdf->Cell($colW[3], $lineH, $money(($newStudentTotal ) ?? 0), 0, 1, 'R');
            }
            // ROW 5
            $pdf->SetFont('Helvetica', 'B', 7);
            $y += $lineH;
            $pdf->SetXY($colX[0], $y);
            $pdf->Cell($colW[0], $lineH, 'Total ', 0, 1, 'L');
            $pdf->SetFont('Helvetica', 'BU', 7);
            $pdf->SetXY($colX[1], $y);
            $pdf->Cell($colW[1], $lineH, $money($summary['total_due'] ?? 0), 0, 1, 'R');                  
            $pdf->SetXY($colX[2], $y);
            $pdf->Cell($colW[2], $lineH, $money(($inst['total_installment50']) ?? 0), 0, 1, 'R');
            $pdf->SetXY($colX[3], $y);
            $pdf->Cell($colW[3], $lineH, $money(($inst['total_installment30'] ) ?? 0), 0, 1, 'R');
            $pdf->SetFont('Helvetica', '', 7);
            //ROW 6
            $y += $lineH;
            $pdf->SetXY($colX[0], $y);
            $pdf->Cell($colW[0], $lineH, 'DOWN PAYMENT', 0, 1, 'L');
            $pdf->SetXY($colX[2], $y);
            $pdf->Cell($colW[2], $lineH, $inst['down_payment50'], 0, 1, 'R');
            $pdf->SetXY($colX[3], $y);
            $pdf->Cell($colW[3], $lineH, $inst['down_payment30'], 0, 1, 'R');
            $y += $lineH;
            // INSTALLMENT ROWS
            $labels = ['1st INSTALLMENT','2nd INSTALLMENT','3rd INSTALLMENT','4th INSTALLMENT','5th INSTALLMENT'];
            $ifee30 = (float)($inst['installment_fee30'] ?? 0);
            $ifee50 = (float)($inst['installment_fee50'] ?? 0);
            foreach ($labels as $lbl) {
                $pdf->SetXY($colX[0], $y);
                $pdf->Cell($colW[0], $lineH, $lbl, 0, 1, 'L');
                $pdf->SetXY($colX[1], $y);
                $pdf->Cell($colW[1], $lineH, '', 0, 1, 'R');
                $pdf->SetXY($colX[2], $y);
                $pdf->Cell($colW[2], $lineH, $money($ifee50), 0, 1, 'R');
                $pdf->SetXY($colX[3], $y);
                $pdf->Cell($colW[3], $lineH, $money($ifee30), 0, 1, 'R');
                $y += $lineH;

            }
            $pdf->SetFont('Helvetica', 'BU', 7);
            $pdf->SetXY($colX[2], $y);
            $pdf->Cell($colW[2], $lineH, $inst['total_installment50'], 0, 1, 'R');
            $pdf->SetXY($colX[3], $y);
            $pdf->Cell($colW[3], $lineH, $inst['total_installment30'], 0, 1, 'R');
            $pdf->SetFont('Helvetica', '', 7);                        

           
        }
        // Footer and policy block (generated from screenshot)
        try {
            // Determine acting registrar/cashier name
            $actorId = $this->ctx->resolveUserId($request);
            if ($actorId === null) {
                $xfac = $request->header('X-Faculty-ID');
                if ($xfac !== null && is_numeric($xfac)) {
                    $actorId = (int) $xfac;
                }
            }
            $actorName = null;
            if ($actorId !== null) {
                $fac = DB::table('tb_mas_faculty')->where('intID', (int)$actorId)->first();
                if ($fac) {
                    $fFirst = trim((string)($fac->strFirstname ?? ''));
                    $fLast  = trim((string)($fac->strLastname ?? ''));
                    $actorName = trim($fFirst . ' ' . $fLast);
                    if ($actorName === '') {
                        $actorName = isset($fac->name) ? (string) $fac->name : null;
                    }
                }
            }

            $generated = now()->format('Y-m-d h:i A');
            if ($actorName) {
                $generated .= ' by ' . $actorName;
            }

            // Start Y anchor for footer content; keep within page
            $startY = 180; // ensure footer begins low enough            

            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Helvetica','',7.5);

            // Row 1: OR number/date and Enrollment Confirmed by (placeholders)
            $pdf->SetXY(5, $startY);
            $pdf->Cell(90, 4.5, 'Official Receipt Number/date ________________________________', 0, 0, 'L');

            $pdf->SetXY(115, $startY);
            $pdf->Cell(85, 4.5, 'Enrollment Confirmed by: ________________________________', 0, 1, 'L');

            // Row 2: dual signature lines + captions
            $y2 = $startY + 8;
            // left line
            $pdf->SetXY(5, $y2);
            $pdf->Cell(90, 0, '_________________________________________', 0, 1, 'L');
            $pdf->SetXY(5, $y2 + 2.8);
            $pdf->Cell(90, 3.5, 'Authorized Signatory', 0, 1, 'L');
            // right line
            $pdf->SetXY(115, $y2);
            $pdf->Cell(90, 0, '_________________________________________', 0, 1, 'L');
            $pdf->SetXY(115, $y2 + 2.8);
            $pdf->Cell(90, 3.5, 'Registrar', 0, 1, 'L');

            // Row 3: Note and Generated text
            $y3 = $y2 + 10;
            $pdf->SetXY(5, $y3);
            $pdf->Cell(100, 4.5, 'Note: Class schedule is subject to change', 0, 0, 'L');

            $pdf->SetXY(115, $y3);
            $pdf->Cell(85, 4.5, 'Generated: ' . $generated, 0, 1, 'L');

            // Policy header and body
            $y4 = $y3 + 6;
            $pdf->SetFont('Helvetica','',7.5);
            $pdf->SetXY(5, $y4);
            $policy = "I shall abide by all existing rules and regulations of the School and those that may be promulgated from time to time. I understand that the school has to collect my personal data and I allow the school to process all my information and all purposes related to this.";
            $pdf->MultiCell(195, 3.8, $policy, 0, 'L');

            $y5 = $pdf->GetY() + 2;
            $pdf->SetFont('Helvetica','',7.5);
            $pdf->SetXY(5, $y5);
            $pdf->Cell(120, 4.5, 'Policy on School Charges and Refund of Fees', 0, 1, 'L');

            $y5 += 4.5;
            $pdf->SetXY(5, $y5);
            $rules1 = "Officially Enrolled Students who withdraw their enrollment before the official start of classes shall be charged a Withdrawal Fee of two thousand five hundred pesos (PhP 2,500.00). Officially Enrolled Students who withdraw their enrollment after the official start of classes, and have already paid the pertinent tuition and other school fees in full or for any length longer than one month (regardless of whether or not he has actually attended classes) shall be charged the appropriate retention fee as stipulated in CHED Manual of Regulations for Private Higher Education (MORPHE) of 2009, as follows:";
            $pdf->MultiCell(195, 3.8, $rules1, 0, 'L');

            // Bulleted list
            $y6 = $pdf->GetY() + 1.5;
            $pdf->SetXY(10, $y6);
            $pdf->Cell(185, 3.8, chr(149) . ' Within the first week of classes - twenty-five percent (25%) of the total school fees.', 0, 1, 'L');
            $pdf->SetXY(10, $y6 + 4);
            $pdf->Cell(185, 3.8, chr(149) . ' Within the second week of classes - fifty percent (50%) of the total school fees.', 0, 1, 'L');
            $pdf->SetXY(10, $y6 + 8);
            $pdf->Cell(185, 3.8, chr(149) . ' Beyond the second week of classes - one hundred percent (100%) of the total school fees.', 0, 1, 'L');

            $y7 = $y6 + 13;
            $pdf->SetXY(5, $y7);
            $pdf->MultiCell(195, 3.8, 'One-time penalty for the late enrollment (PhP 500.00) shall be charged after the first day of official start of classes per term.', 0, 'L');

            // Bottom centered student signature
            $yBottom = $pdf->GetY() + 15;
            
            $pdf->SetXY(70, $yBottom);
            $pdf->Cell(70, 0, '__________________________________________', 0, 1, 'C');
            $pdf->SetXY(70, $yBottom + 3);
            $pdf->Cell(70, 4, 'Student Signature/Date', 0, 1, 'C');

        } catch (\Throwable $e) {
            // Do not block PDF on footer render issues
        }

        // Stream inline with filename
        $safeSn = preg_replace('/[^A-Za-z0-9\-]/', '', $sn);
        $filename = 'reg-form-' . ($safeSn ?: 'SN') . '-' . $syid . '.pdf';
        $content = $pdf->Output('S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
