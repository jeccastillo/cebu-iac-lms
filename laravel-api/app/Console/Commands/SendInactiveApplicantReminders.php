<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\PHPMailerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInactiveApplicantReminders extends Command
{
    protected $signature = 'reminders:inactive-applicants {--limit= : Limit number of emails to send for testing} {--simulate : Simulate sending without actually sending emails}';
    protected $description = 'Send reminders to applicants with inactive applications (no status changes or payments for 2+ days)';

    protected $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        parent::__construct();
        $this->phpMailerService = $phpMailerService;
    }

    public function handle()
    {
        $this->info('🔍 Checking for inactive applicants in tb_mas_applicant_data...');
        
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $simulate = $this->option('simulate');
        
        if ($limit) {
            $this->info("📧 Limited to {$limit} emails for testing.");
        }
        
        if ($simulate) {
            $this->warn("🧪 SIMULATION MODE - No emails will actually be sent.");
        }
        
        $twoDaysAgo = Carbon::now()->subDays(2);
        
        // Get applicants that haven't moved for 2+ days and haven't received reminder recently
        $inactiveApplicants = DB::table('tb_mas_applicant_data as ad')
            ->join('tb_mas_users as u', 'u.intID', '=', 'ad.user_id')
            ->where('ad.created_at', '<=', $twoDaysAgo)
            ->whereIn('ad.status', ['New', 'For Interview', 'For Requirements', 'Applied'])
            ->where(function($query) use ($twoDaysAgo) {
                $query->whereNull('ad.last_inactive_reminder_sent')
                      ->orWhere('ad.last_inactive_reminder_sent', '<=', $twoDaysAgo);
            })
            ->select(
                'ad.id as applicant_data_id',
                'ad.user_id',
                'ad.status',
                'ad.created_at',
                'ad.updated_at',
                'ad.last_inactive_reminder_sent',
                'ad.inactive_reminder_count',
                'u.strFirstname as first_name',
                'u.strLastname as last_name', 
                'u.strEmail as email'
            )
            ->orderBy('ad.created_at', 'asc');

        if ($limit) {
            $inactiveApplicants = $inactiveApplicants->limit($limit);
        }

        $applicants = $inactiveApplicants->get();
        
        $this->info("📊 Found {$applicants->count()} inactive applicants");

        if ($applicants->isEmpty()) {
            $this->info('✅ No inactive applicants found.');
            return 0;
        }

        $sentCount = 0;

        foreach ($applicants as $applicant) {
            // Check if there have been any recent payments for this user
            $hasRecentPayment = $this->hasRecentPayment($applicant->user_id, $twoDaysAgo);
            
            if ($hasRecentPayment) {
                $this->info("💰 Skipping applicant {$applicant->applicant_data_id} - has recent payment");
                continue;
            }

            $applicationNumber = 'A' . str_pad($applicant->applicant_data_id, 6, '0', STR_PAD_LEFT);
            $applicantName = trim(($applicant->first_name ?? '') . ' ' . ($applicant->last_name ?? ''));
            
            if ($simulate) {
                $this->info("📧 [SIMULATE] Would send inactive reminder to: {$applicantName} ({$applicant->email}) - App: {$applicationNumber}");
            } else {
                try {
                    // Send the reminder email
                    $this->phpMailerService->sendInactiveApplicationReminder([
                        'applicant_name' => $applicantName,
                        'application_number' => $applicationNumber,
                        'email' => $applicant->email,
                        'status' => $applicant->status,
                        'days_inactive' => Carbon::parse($applicant->updated_at)->diffInDays(now())
                    ]);

                    // Update reminder tracking
                    DB::table('tb_mas_applicant_data')
                        ->where('id', $applicant->applicant_data_id)
                        ->update([
                            'last_inactive_reminder_sent' => now(),
                            'inactive_reminder_count' => ($applicant->inactive_reminder_count ?? 0) + 1,
                            'updated_at' => now()
                        ]);

                    $this->info("✅ Sent inactive reminder to: {$applicantName} ({$applicant->email})");
                    $sentCount++;

                } catch (\Exception $e) {
                    $this->error("❌ Failed to send reminder to {$applicant->email}: " . $e->getMessage());
                    Log::error('Inactive applicant reminder failed', [
                        'applicant_id' => $applicant->applicant_data_id,
                        'email' => $applicant->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Add small delay to prevent overwhelming email server
            if (!$simulate) {
                usleep(500000); // 0.5 second delay
            }
        }

        $verb = $simulate ? 'Would send' : 'Sent';
        $this->info("🎯 {$verb} {$sentCount} inactive application reminders");
        
        Log::info('Inactive applicant reminders completed', [
            'total_found' => $applicants->count(),
            'emails_sent' => $sentCount,
            'simulated' => $simulate
        ]);

        return 0;
    }

    /**
     * Check if user has made any payments in the last 2 days
     */
    private function hasRecentPayment(int $userId, Carbon $since): bool
    {
        if (!DB::getSchemaBuilder()->hasTable('payment_details')) {
            return false;
        }

        return DB::table('payment_details')
            ->where('student_information_id', $userId)
            ->where('created_at', '>', $since)
            ->exists();
    }
}
