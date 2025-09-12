<?php

namespace App\Services;

use App\Models\ApplicantInterview;
use App\Services\SystemLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

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

        // Send interview schedule notification email (best-effort)
        try {
            $this->sendInterviewScheduleNotification($interview);
        } catch (\Throwable $e) {
            // ignore email sending failures
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

            // If failed, set status to 'Rejected' when column exists
            if ($assessment === ApplicantInterview::ASSESSMENT_FAILED) {
                try {
                    if (Schema::hasTable('tb_mas_applicant_data') && Schema::hasColumn('tb_mas_applicant_data', 'status')) {
                        DB::table('tb_mas_applicant_data')
                            ->where('id', $interview->applicant_data_id)
                            ->update(['status' => 'Rejected']);
                    }
                } catch (\Throwable $e) {
                    // ignore failures
                }
            }

            DB::commit();

            // Send result notification emails (best-effort)
            try {
                if ($assessment === ApplicantInterview::ASSESSMENT_FAILED) {
                    $this->sendInterviewFailedNotification($interview);
                } else {
                    $this->sendInterviewPassedNotification($interview);
                }
            } catch (\Throwable $e) {
                // ignore email sending failures  
            }

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

    /**
     * Send interview schedule notification email
     */
    protected function sendInterviewScheduleNotification(ApplicantInterview $interview): void
    {
        $applicantData = $this->getApplicantEmailAndName($interview->applicant_data_id);
        if (!$applicantData) {
            return;
        }

        [$email, $name] = $applicantData;

        try {
            $phpMailerService = app(\App\Services\PHPMailerService::class);
            $phpMailerService->sendInterviewScheduleNotification(
                $email,
                $name,
                $interview->scheduled_at ? $interview->scheduled_at->format('Y-m-d H:i:s') : 'TBD',
                $interview->remarks
            );
        } catch (\Exception $e) {
            // Fallback to basic Laravel mail
            $subject = 'iACADEMY Interview Scheduled';
            $body = "Hello {$name},\n\n"
                . "Your interview has been scheduled.\n\n"
                . "Date & Time: " . ($interview->scheduled_at ? $interview->scheduled_at->format('F j, Y \a\t g:i A') : 'TBD') . "\n\n"
                . ($interview->remarks ? "Remarks: {$interview->remarks}\n\n" : "")
                . "Please prepare for your interview. We look forward to meeting you.\n\n"
                . "Best regards,\n"
                . "iACADEMY Admissions Team";

            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email);
                $message->from('josephedmundcastillo@gmail.com', 'iACADEMY Admissions');
                $message->subject($subject);
            });
        }
    }

    /**
     * Send interview passed notification email
     */
    protected function sendInterviewPassedNotification(ApplicantInterview $interview): void
    {
        $applicantData = $this->getApplicantEmailAndName($interview->applicant_data_id);
        if (!$applicantData) {
            return;
        }

        [$email, $name] = $applicantData;

        try {
            $phpMailerService = app(\App\Services\PHPMailerService::class);
            $phpMailerService->sendInterviewPassedNotification(
                $email,
                $name,
                $interview->remarks
            );
        } catch (\Exception $e) {
            // Fallback to basic Laravel mail
            $subject = 'iACADEMY Interview Results - Congratulations!';
            $body = "Hello {$name},\n\n"
                . "Congratulations! You have successfully passed your interview.\n\n"
                . ($interview->remarks ? "Comments: {$interview->remarks}\n\n" : "")
                . "Our Admissions Team will contact you soon with next steps.\n\n"
                . "Welcome to iACADEMY!\n\n"
                . "Best regards,\n"
                . "iACADEMY Admissions Team";

            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email);
                $message->from('josephedmundcastillo@gmail.com', 'iACADEMY Admissions');
                $message->subject($subject);
            });
        }
    }

    /**
     * Send interview failed notification email
     */
    protected function sendInterviewFailedNotification(ApplicantInterview $interview): void
    {
        $applicantData = $this->getApplicantEmailAndName($interview->applicant_data_id);
        if (!$applicantData) {
            return;
        }

        [$email, $name] = $applicantData;

        try {
            $phpMailerService = app(\App\Services\PHPMailerService::class);
            $phpMailerService->sendInterviewFailedNotification(
                $email,
                $name,
                $interview->reason_for_failing
            );
        } catch (\Exception $e) {
            // Fallback to basic Laravel mail
            $subject = 'iACADEMY Interview Results';
            $body = "Hello {$name},\n\n"
                . "Thank you for taking the time to interview with us.\n\n"
                . "After careful consideration, we regret to inform you that we will not be moving forward with your application at this time.\n\n"
                . ($interview->reason_for_failing ? "Feedback: {$interview->reason_for_failing}\n\n" : "")
                . "We encourage you to continue developing your skills and consider reapplying in the future.\n\n"
                . "Best regards,\n"
                . "iACADEMY Admissions Team";

            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email);
                $message->from('josephedmundcastillo@gmail.com', 'iACADEMY Admissions');
                $message->subject($subject);
            });
        }

        // Additional Journey log for failed interviews
        try {
            app(\App\Services\ApplicantJourneyService::class)
                ->log((int) $interview->applicant_data_id, 'Application Rejected');
        } catch (\Throwable $e) {
            // ignore logging failures
        }
    }

    /**
     * Get applicant email and name from user data
     */
    protected function getApplicantEmailAndName(int $applicantDataId): ?array
    {
        try {
            $appDataRow = DB::table('tb_mas_applicant_data')->where('id', $applicantDataId)->first();
            if (!$appDataRow || !isset($appDataRow->user_id)) {
                return null;
            }

            $user = DB::table('tb_mas_users')->where('intID', $appDataRow->user_id)->first();
            if (!$user || !isset($user->strEmail) || !is_string($user->strEmail)) {
                return null;
            }

            $email = trim((string) $user->strEmail);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            $name = trim(
                (($user->strFirstname ?? '') . ' ' .
                ($user->strMiddlename ?? '') . ' ' .
                ($user->strLastname ?? ''))
            );
            $name = $name !== '' ? $name : 'Student';

            return [$email, $name];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
