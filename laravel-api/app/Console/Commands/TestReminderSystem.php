<?php

namespace App\Console\Commands;

use App\Models\Admissions\AdmissionStudentInformation;
use App\Models\ApplicantInterview;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestReminderSystem extends Command
{
    protected $signature = 'test:reminder-system {--dry-run : Show what would be sent without actually sending emails} {--limit= : Limit number of emails to send for testing} {--simulate : Simulate sending without actually sending emails}';
    protected $description = 'Test the reminder system and show statistics';

    public function handle()
    {
        $this->info('🔍 Testing Reminder System...');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $simulate = $this->option('simulate');
        
        if ($isDryRun) {
            $this->warn('🔄 DRY RUN MODE - No emails will be sent');
            $this->newLine();
        } elseif ($limit || $simulate) {
            if ($limit) {
                $this->warn("📧 TESTING MODE - Limited to {$limit} emails");
            }
            if ($simulate) {
                $this->warn("🔄 SIMULATION MODE - No emails will actually be sent");
            }
            $this->newLine();
        }

        // Check inactive applications
        $this->info('📊 INACTIVE APPLICATIONS ANALYSIS:');
        $this->line('=====================================');
        
        $twoDaysAgo = Carbon::now()->subDays(2);
        
        $inactiveApplications = AdmissionStudentInformation::where('created_at', '<=', $twoDaysAgo)
            ->whereIn('status', ['New', 'For Interview', 'For Requirements'])
            ->get();

        $eligibleForReminder = 0;
        
        foreach ($inactiveApplications as $application) {
            $recentPayments = PaymentDetail::where('student_information_id', $application->id)
                ->where('created_at', '>=', $twoDaysAgo)
                ->count();

            $statusUpdatedRecently = $application->updated_at >= $twoDaysAgo;
            
            $lastReminderSent = $application->last_inactive_reminder_sent 
                ? Carbon::parse($application->last_inactive_reminder_sent) 
                : null;
            
            $reminderSentRecently = $lastReminderSent && $lastReminderSent >= $twoDaysAgo;

            if ($recentPayments == 0 && !$statusUpdatedRecently && !$reminderSentRecently) {
                $eligibleForReminder++;
                
                if ($isDryRun) {
                    $daysInactive = Carbon::parse($application->updated_at)->diffInDays(Carbon::now());
                    $this->line("  → {$application->email} (Status: {$application->status}, Inactive: {$daysInactive} days)");
                }
            }
        }

        $this->info("Total inactive applications: {$inactiveApplications->count()}");
        $this->info("Eligible for reminder: {$eligibleForReminder}");
        $this->newLine();

        // Check interviewed not reserved
        $this->info('📊 INTERVIEWED NOT RESERVED ANALYSIS:');
        $this->line('===================================');
        
        $interviewedNotReserved = AdmissionStudentInformation::where('status', 'For Reservation')
            ->where('updated_at', '<=', $twoDaysAgo)
            ->get();

        $eligibleForReservationReminder = 0;
        
        foreach ($interviewedNotReserved as $application) {
            $lastReminderSent = $application->last_reservation_reminder_sent 
                ? Carbon::parse($application->last_reservation_reminder_sent) 
                : null;
            
            $reminderSentRecently = $lastReminderSent && $lastReminderSent >= $twoDaysAgo;

            $interview = ApplicantInterview::where('applicant_data_id', $application->id)
                ->where('assessment', ApplicantInterview::ASSESSMENT_PASSED)
                ->latest()
                ->first();

            if (!$reminderSentRecently && $interview) {
                $eligibleForReservationReminder++;
                
                if ($isDryRun) {
                    $daysSinceInterview = Carbon::parse($interview->updated_at)->diffInDays(Carbon::now());
                    $this->line("  → {$application->email} (Days since interview: {$daysSinceInterview})");
                }
            }
        }

        $this->info("Total interviewed not reserved: {$interviewedNotReserved->count()}");
        $this->info("Eligible for reservation reminder: {$eligibleForReservationReminder}");
        $this->newLine();

        if (!$isDryRun) {
            $this->info('🚀 Running reminder commands...');
            
            // Run the actual commands with limit and simulate if specified
            $commandArgs = [];
            if ($limit) {
                $commandArgs['--limit'] = $limit;
            }
            if ($simulate) {
                $commandArgs['--simulate'] = true;
            }
            
            $this->call('reminders:inactive-applications', $commandArgs);
            $this->call('reminders:interviewed-not-reserved', $commandArgs);
        } else {
            $this->info('💡 To actually send emails, run: php artisan test:reminder-system');
        }
        
        $this->newLine();
        $this->info('✅ Reminder system test completed!');

        return 0;
    }
}
