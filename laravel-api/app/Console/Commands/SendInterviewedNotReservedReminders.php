<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admissions\AdmissionStudentInformation;
use App\Models\ApplicantInterview;
use App\Services\PHPMailerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInterviewedNotReservedReminders extends Command
{
    protected $signature = 'reminders:interviewed-not-reserved {--limit= : Limit number of emails to send for testing} {--simulate : Simulate sending without actually sending emails}';
    protected $description = 'Send reminders to applicants who passed interview but have not reserved for 2+ days';

    protected $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        parent::__construct();
        $this->phpMailerService = $phpMailerService;
    }

    public function handle()
    {
        $this->info('Checking for interviewed but not reserved applicants...');
        
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $simulate = $this->option('simulate');
        
        if ($limit) {
            $this->info("Limited to {$limit} emails for testing.");
        }
        
        if ($simulate) {
            $this->warn("SIMULATION MODE - No emails will actually be sent.");
        }
        
        $twoDaysAgo = Carbon::now()->subDays(2);
        
        // Get applications that passed interview but haven't reserved
        $interviewedNotReserved = AdmissionStudentInformation::where('status', 'For Reservation')
            ->where('updated_at', '<=', $twoDaysAgo)
            ->get();

        $sentCount = 0;

        foreach ($interviewedNotReserved as $application) {
            // Check if we already sent a reminder in the last 2 days
            $lastReminderSent = $application->last_reservation_reminder_sent 
                ? Carbon::parse($application->last_reservation_reminder_sent) 
                : null;
            
            $reminderSentRecently = $lastReminderSent && $lastReminderSent >= $twoDaysAgo;

            // Get interview details - check if student was interviewed using applicant_data_id
            $interview = ApplicantInterview::where('applicant_data_id', $application->id)
                ->where('assessment', ApplicantInterview::ASSESSMENT_PASSED)
                ->latest()
                ->first();

            if (!$reminderSentRecently && $interview) {
                // Check limit
                if ($limit && $sentCount >= $limit) {
                    $this->info("Reached limit of {$limit} emails. Stopping.");
                    break;
                }
                
                try {
                    $daysSinceInterview = Carbon::parse($interview->updated_at)->diffInDays(Carbon::now());
                    
                    // Get tb_mas_applicant_data ID for proper application number generation
                    $applicantData = null;
                    if ($application->email) {
                        // Try to find by email in tb_mas_users first, then get applicant_data
                        $user = \Illuminate\Support\Facades\DB::table('tb_mas_users')->where('strEmail', $application->email)->first();
                        if ($user) {
                            $applicantData = \Illuminate\Support\Facades\DB::table('tb_mas_applicant_data')->where('user_id', $user->intID)->first();
                        }
                    }
                    
                    // Fallback: use admission_student_information ID if applicant data not found
                    $applicationNumber = $applicantData 
                        ? 'A' . str_pad($applicantData->id, 6, '0', STR_PAD_LEFT)
                        : 'A' . str_pad($application->id, 6, '0', STR_PAD_LEFT);
                    
                    if ($simulate) {
                        // Simulate sending email
                        $this->info("SIMULATED: Would send reservation reminder to: {$application->email}");
                        $result = true; // Simulate success
                    } else {
                        $result = $this->phpMailerService->sendInterviewedNotReservedReminder([
                            'email' => $application->email,
                            'name' => $application->first_name . ' ' . $application->last_name,
                            'application_number' => $applicationNumber,
                            'interview_date' => $interview->updated_at,
                            'days_since_interview' => $daysSinceInterview,
                            'program' => $application->desiredProgram ? $application->desiredProgram->name : 'Your chosen program'
                        ]);
                    }

                    if ($result) {
                        // Update last reminder sent timestamp (only if not simulating)
                        if (!$simulate) {
                            $application->last_reservation_reminder_sent = Carbon::now();
                            $application->save();
                        }

                        $sentCount++;
                        if (!$simulate) {
                            $this->info("Sent reservation reminder to: {$application->email}");
                        }
                    } else {
                        $this->error("Failed to send reminder to: {$application->email}");
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to send reservation reminder to {$application->email}: " . $e->getMessage());
                    $this->error("Failed to send reminder to: {$application->email}");
                }
            }
        }

        $this->info("Sent {$sentCount} reservation reminders." . ($simulate ? " (SIMULATED)" : ""));
        if (!$simulate) {
            Log::info("Sent {$sentCount} reservation reminders via CRON job.");
        }

        return 0;
    }
}
