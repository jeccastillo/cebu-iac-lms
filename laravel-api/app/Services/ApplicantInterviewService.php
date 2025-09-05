<?php

namespace App\Services;

use App\Models\ApplicantInterview;
use App\Services\SystemLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantInterviewService
{
    /**
     * Schedule a single interview for a given applicant_data_id.
     * Enforces uniqueness (one interview per applicant_data_id).
     *
     * @throws \RuntimeException when duplicate schedule exists or applicant_data missing
     */
    public function schedule(
        int $applicantDataId,
        $scheduledAt,
        ?int $interviewerUserId,
        ?string $remarks,
        Request $ctx
    ): ApplicantInterview {
        // Ensure applicant_data exists
        $appData = DB::table('tb_mas_applicant_data')->where('id', $applicantDataId)->first();
        if (!$appData) {
            throw new \RuntimeException('Applicant data not found for id=' . $applicantDataId);
        }

        // One interview per applicant
        $existing = DB::table('tb_mas_applicant_interviews')
            ->where('applicant_data_id', $applicantDataId)
            ->first();
        if ($existing) {
            throw new \RuntimeException('Interview already scheduled for this applicant.');
        }

        // Parse schedule
        $scheduled = $this->parseDateTime($scheduledAt);

        // Resolve interviewer when not explicitly provided
        $resolvedInterviewerId = $interviewerUserId;
        if ($resolvedInterviewerId === null) {
            try {
                $resolver = app(\App\Services\UserContextResolver::class);
                $rid = $resolver->resolveUserId($ctx);
                if ($rid !== null) {
                    $resolvedInterviewerId = (int) $rid;
                }
            } catch (\Throwable $e) {
                // ignore resolver errors
            }
        }

        // Create interview
        $interview = ApplicantInterview::create([
            'applicant_data_id'   => $applicantDataId,
            'scheduled_at'        => $scheduled,
            'interviewer_user_id' => $resolvedInterviewerId,
            'remarks'             => $remarks,
            'assessment'          => null,
            'reason_for_failing'  => null,
            'completed_at'        => null,
        ]);

        // System log (guarded)
        try {
            SystemLogService::log(
                'create',
                'ApplicantInterview',
                (int) $interview->id,
                null,
                [
                    'applicant_data_id' => $interview->applicant_data_id,
                    'scheduled_at'      => $interview->scheduled_at,
                    'interviewer_user_id'=> $interview->interviewer_user_id,
                    'remarks'           => $interview->remarks,
                ],
                $ctx
            );
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        // Journey log (guarded): Interview Scheduled
        try {
            app(\App\Services\ApplicantJourneyService::class)
                ->log((int) $interview->applicant_data_id, 'Interview Scheduled');
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return $interview;
    }

    /**
     * Submit interview result (assessment, remarks, reason_for_failing, completed_at).
     * Atomically sets tb_mas_applicant_data.interviewed = true with the interview update.
     *
     * @throws \RuntimeException when interview missing, already completed, or invalid assessment
     */
    public function submitResult(
        int $interviewId,
        string $assessment,
        ?string $remarks,
        ?string $reasonForFailing,
        $completedAt,
        Request $ctx
    ): ApplicantInterview {
        $interview = ApplicantInterview::query()->find($interviewId);
        if (!$interview) {
            throw new \RuntimeException('Interview not found.');
        }

        if ($interview->completed_at !== null) {
            throw new \RuntimeException('Interview result already submitted.');
        }

        // Validate assessment defensively (requests already validate)
        $allowed = [ApplicantInterview::ASSESSMENT_PASSED, ApplicantInterview::ASSESSMENT_FAILED];
        if (!in_array($assessment, $allowed, true)) {
            throw new \RuntimeException('Invalid assessment value.');
        }

        if ($assessment === ApplicantInterview::ASSESSMENT_FAILED) {
            if ($reasonForFailing === null || trim($reasonForFailing) === '') {
                throw new \RuntimeException('Reason for failing is required when assessment is Failed.');
            }
        } else {
            // Passed => clear failing reason
            $reasonForFailing = null;
        }

        $completed = $completedAt ? $this->parseDateTime($completedAt) : Carbon::now();

        // Transactional update: interview + flip interviewed flag
        DB::beginTransaction();
        try {
            $before = $interview->toArray();

            $interview->assessment = $assessment;
            $interview->remarks = $remarks;
            $interview->reason_for_failing = $reasonForFailing;
            $interview->completed_at = $completed;
            $interview->save();

            DB::table('tb_mas_applicant_data')
                ->where('id', $interview->applicant_data_id)
                ->update(['interviewed' => true]);

            DB::commit();

            // System log (guarded)
            try {
                SystemLogService::log(
                    'update',
                    'ApplicantInterview',
                    (int) $interview->id,
                    $before,
                    $interview->toArray(),
                    $ctx
                );
            } catch (\Throwable $e) {
                // ignore logging failures
            }

            // Journey log (guarded): Interview Result: Passed/Failed
            try {
                app(\App\Services\ApplicantJourneyService::class)
                    ->log((int) $interview->applicant_data_id, 'Interview Result: ' . ucfirst(strtolower($assessment)));
            } catch (\Throwable $e) {
                // ignore logging failures
            }

            return $interview->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Convenience: fetch interview by applicant_data_id (single row).
     */
    public function getByApplicantDataId(int $applicantDataId): ?ApplicantInterview
    {
        return ApplicantInterview::query()
            ->where('applicant_data_id', $applicantDataId)
            ->first();
    }

    /**
     * Utility: parse datetime input into Carbon instance.
     *
     * @param mixed $value
     */
    protected function parseDateTime($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTime::createFromInterface($value));
        }
        $ts = strtotime((string) $value);
        if ($ts === false) {
            throw new \RuntimeException('Invalid date/time value: ' . (string) $value);
        }
        return Carbon::createFromTimestamp($ts);
    }
}
