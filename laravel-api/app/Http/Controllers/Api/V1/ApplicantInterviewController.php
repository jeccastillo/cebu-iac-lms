<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApplicantInterviewScheduleRequest;
use App\Http\Requests\Api\V1\ApplicantInterviewResultRequest;
use App\Models\ApplicantInterview;
use App\Services\ApplicantInterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\UserContextResolver;

class ApplicantInterviewController extends Controller
{
    protected ApplicantInterviewService $service;
    protected UserContextResolver $userResolver;

    public function __construct(ApplicantInterviewService $service, UserContextResolver $userResolver)
    {
        $this->service = $service;
        $this->userResolver = $userResolver;
    }

    /**
     * POST /api/v1/admissions/interviews
     * Schedule a single interview for an applicant_data_id.
     */
    public function store(ApplicantInterviewScheduleRequest $request): JsonResponse
    {
        try {
            $interviewerUserId = $request->input('interviewer_user_id') !== null
                ? (int) $request->input('interviewer_user_id')
                : $this->userResolver->resolveUserId($request);

            $interview = $this->service->schedule(
                (int) $request->input('applicant_data_id'),
                $request->input('scheduled_at'),
                $interviewerUserId,
                $request->input('remarks'),
                $request
            );

            return response()->json([
                'success' => true,
                'data' => $this->serializeInterview($interview),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule interview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/admissions/interviews/{id}
     * Show interview by id.
     */
    public function show(int $id): JsonResponse
    {
        $row = ApplicantInterview::query()->find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Interview not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeInterview($row),
        ]);
    }

    /**
     * GET /api/v1/admissions/applicant-data/{applicantDataId}/interview
     * Show interview by applicant_data_id (single row).
     */
    public function showByApplicantData(int $applicantDataId): JsonResponse
    {
        $row = $this->service->getByApplicantDataId($applicantDataId);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Interview not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->serializeInterview($row),
        ]);
    }

    /**
     * PUT /api/v1/admissions/interviews/{id}/result
     * Submit interview result (assessment, remarks, reason_for_failing, completed_at).
     */
    public function submitResult(ApplicantInterviewResultRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->service->submitResult(
                $id,
                (string) $request->input('assessment'),
                $request->input('remarks'),
                $request->input('reason_for_failing'),
                $request->input('completed_at'),
                $request
            );

            // Fetch interviewed flag from applicant_data
            $interviewed = null;
            try {
                $interviewed = DB::table('tb_mas_applicant_data')
                    ->where('id', $updated->applicant_data_id)
                    ->value('interviewed');
            } catch (\Throwable $e) {
                $interviewed = null;
            }

            return response()->json([
                'success' => true,
                'data' => $this->serializeInterview($updated) + [
                    'applicant_data_interviewed' => (bool) $interviewed,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit interview result: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Normalize interview payload.
     */
    protected function serializeInterview(ApplicantInterview $row): array
    {
        // Attempt to resolve interviewer full name from legacy tb_mas_users table
        $interviewerName = null;
        try {
            if ($row->interviewer_user_id !== null) {
                $u = DB::table('tb_mas_faculty')
                    ->where('intID', (int) $row->interviewer_user_id)
                    ->select('strFirstname', 'strMiddlename', 'strLastname')
                    ->first();
                if ($u) {
                    $parts = [];
                    if (isset($u->strFirstname) && $u->strFirstname !== '') $parts[] = $u->strFirstname;
                    if (isset($u->strMiddlename) && $u->strMiddlename !== '') $parts[] = $u->strMiddlename;
                    if (isset($u->strLastname) && $u->strLastname !== '') $parts[] = $u->strLastname;
                    $name = trim(implode(' ', array_filter($parts, function ($x) { return $x !== null && $x !== ''; })));
                    if ($name !== '') {
                        $interviewerName = $name;
                    }
                }
            }
        } catch (\Throwable $e) {
            $interviewerName = null; // best-effort only
        }

        return [
            'id' => (int) $row->id,
            'applicant_data_id' => (int) $row->applicant_data_id,
            'scheduled_at' => optional($row->scheduled_at)->toDateTimeString(),
            'interviewer_user_id' => $row->interviewer_user_id !== null ? (int) $row->interviewer_user_id : null,
            'interviewer_name' => $interviewerName,
            'remarks' => $row->remarks,
            'assessment' => $row->assessment,
            'reason_for_failing' => $row->reason_for_failing,
            'completed_at' => $row->completed_at ? $row->completed_at->toDateTimeString() : null,
            'created_at' => optional($row->created_at)->toDateTimeString(),
            'updated_at' => optional($row->updated_at)->toDateTimeString(),
        ];
    }
}
