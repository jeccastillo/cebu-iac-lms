<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admissions\AdmissionStudentInformation;
use App\Models\PaymentDetail;
use App\Services\PHPMailerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInactiveApplicationReminders extends Command
{
    protected $signature = 'reminders:inactive-applications {--limit= : Limit number of emails to send for testing} {--simulate : Simulate sending without actually sending emails}';
    protected $description = 'Send reminders to applicants with inactive applications (no status changes or payments for 2+ days)';

    protected $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        parent::__construct();
        $this->phpMailerService = $phpMailerService;
    }

    public function handle()
    {
        $this->info('Checking for inactive applications...');
        
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $simulate = $this->option('simulate');
        
        if ($limit) {
            $this->info("Limited to {$limit} emails for testing.");
        }
        
        if ($simulate) {
            $this->warn("SIMULATION MODE - No emails will actually be sent.");
        }
        
        // Get applications that are older than 2 days and haven't moved
        $twoDaysAgo = Carbon::now()->subDays(2);
        
        $inactiveApplications = AdmissionStudentInformation::where('created_at', '<=', $twoDaysAgo)
            ->whereIn('status', ['New', 'For Interview', 'For Requirements'])
            ->get();

        $sentCount = 0;

        foreach ($inactiveApplications as $application) {
            // Check if there have been any recent payments
            $recentPayments = PaymentDetail::where('student_information_id', $application->id)
                ->where('created_at', '>=', $twoDaysAgo)
                ->count();

            // Check if status was updated recently
            $statusUpdatedRecently = $application->updated_at >= $twoDaysAgo;

            // Check if we already sent a reminder in the last 2 days
            $lastReminderSent = $application->last_inactive_reminder_sent 
                ? Carbon::parse($application->last_inactive_reminder_sent) 
                : null;
            
            $reminderSentRecently = $lastReminderSent && $lastReminderSent >= $twoDaysAgo;

            // Send reminder if no recent activity and no recent reminder
            if ($recentPayments == 0 && !$statusUpdatedRecently && !$reminderSentRecently) {
                // Check limit
                if ($limit && $sentCount >= $limit) {
                    $this->info("Reached limit of {$limit} emails. Stopping.");
                    break;
                }
                
                try {
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
                        $this->info("SIMULATED: Would send inactive reminder to: {$application->email}");
                        $result = true; // Simulate success
                    } else {
                        $result = $this->phpMailerService->sendInactiveApplicationReminder([
                            'email' => $application->email,
                            'name' => $application->first_name . ' ' . $application->last_name,
                            'application_number' => $applicationNumber,
                            'status' => $application->status,
                            'days_inactive' => Carbon::parse($application->updated_at)->diffInDays(Carbon::now())
                        ]);
                    }

                    if ($result) {
                        // Update last reminder sent timestamp (only if not simulating)
                        if (!$simulate) {
                            $application->last_inactive_reminder_sent = Carbon::now();
                            $application->save();
                        }

                        $sentCount++;
                        if (!$simulate) {
                            $this->info("Sent inactive reminder to: {$application->email}");
                        }
                    } else {
                        $this->error("Failed to send reminder to: {$application->email}");
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to send inactive application reminder to {$application->email}: " . $e->getMessage());
                    $this->error("Failed to send reminder to: {$application->email}");
                }
            }
        }

        $this->info("Sent {$sentCount} inactive application reminders." . ($simulate ? " (SIMULATED)" : ""));
        if (!$simulate) {
            Log::info("Sent {$sentCount} inactive application reminders via CRON job.");
        }

        return 0;
    }
}
