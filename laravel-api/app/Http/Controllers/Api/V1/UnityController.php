<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UnityAdvisingRequest;
use App\Http\Requests\Api\V1\UnityEnlistRequest;
use App\Http\Requests\Api\V1\UnityResetRegistrationRequest;
use App\Http\Resources\TuitionBreakdownResource;
use App\Services\TuitionService;
use App\Services\EnlistmentService;
use App\Services\UserContextResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnityController extends Controller
{
    protected TuitionService $tuition;
    protected EnlistmentService $enlistment;
    protected UserContextResolver $ctx;

    public function __construct(TuitionService $tuition, EnlistmentService $enlistment, UserContextResolver $ctx)
    {
        $this->tuition = $tuition;
        $this->enlistment = $enlistment;
        $this->ctx = $ctx;
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
     *  - term: string
     *  - subjects: array of { subject_id: int, section?: string }
     * Returns TuitionBreakdownResource (placeholder breakdown for now).
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
        ]);

        $breakdown = $this->tuition->preview($payload);

        return response()->json([
            'success' => true,
            'data'    => new TuitionBreakdownResource($breakdown),
        ]);
    }
}
