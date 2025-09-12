<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\PHPMailerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendReservationReminders extends Command
{
    protected $signature = 'reminders:reservation-needed {--limit= : Limit number of emails to send for testing} {--simulate : Simulate sending without actually sending emails}';
    protected $description = 'Send reminders to applicants who passed interview but have not reserved for 2+ days';

    protected $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        parent::__construct();
        $this->phpMailerService = $phpMailerService;
    }

    public function handle()
    {
        $this->info('🔍 Checking for interviewed but not reserved applicants...');
        
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $simulate = $this->option('simulate');
        
        if ($limit) {
            $this->info("📧 Limited to {$limit} emails for testing.");
        }
        
        if ($simulate) {
            $this->warn("🧪 SIMULATION MODE - No emails will actually be sent.");
        }
        
        $twoDaysAgo = Carbon::now()->subDays(2);
        
        // Get applicants that passed interview but haven't reserved for 2+ days
        $needReservation = DB::table('tb_mas_applicant_data as ad')
            ->join('tb_mas_users as u', 'u.intID', '=', 'ad.user_id')
            ->where('ad.status', 'For Reservation')
            ->where('ad.updated_at', '<=', $twoDaysAgo)
            ->where(function($query) use ($twoDaysAgo) {
                $query->whereNull('ad.last_reservation_reminder_sent')
                      ->orWhere('ad.last_reservation_reminder_sent', '<=', $twoDaysAgo);
            })
            ->select(
                'ad.id as applicant_data_id',
                'ad.user_id',
                'ad.status',
                'ad.updated_at',
                'ad.last_reservation_reminder_sent',
                'ad.reservation_reminder_count',
                'u.strFirstname as first_name',
                'u.strLastname as last_name',
                'u.strEmail as email'
            )
            ->orderBy('ad.updated_at', 'asc');

        if ($limit) {
            $needReservation = $needReservation->limit($limit);
        }

        $applicants = $needReservation->get();
        
        $this->info("📊 Found {$applicants->count()} applicants needing reservation reminders");

        if ($applicants->isEmpty()) {
            $this->info('✅ No applicants needing reservation reminders found.');
            return 0;
        }

        $sentCount = 0;

        foreach ($applicants as $applicant) {
            $applicationNumber = 'A' . str_pad($applicant->applicant_data_id, 6, '0', STR_PAD_LEFT);
            $applicantName = trim(($applicant->first_name ?? '') . ' ' . ($applicant->last_name ?? ''));
            
            if ($simulate) {
                $this->info("📧 [SIMULATE] Would send reservation reminder to: {$applicantName} ({$applicant->email}) - App: {$applicationNumber}");
            } else {
                try {
                    // Send the reminder email
                    $this->phpMailerService->sendInterviewedNotReservedReminder([
                        'applicant_name' => $applicantName,
                        'application_number' => $applicationNumber,
                        'email' => $applicant->email,
                        'days_since_interview' => Carbon::parse($applicant->updated_at)->diffInDays(now())
                    ]);

                    // Update reminder tracking
                    DB::table('tb_mas_applicant_data')
                        ->where('id', $applicant->applicant_data_id)
                        ->update([
                            'last_reservation_reminder_sent' => now(),
                            'reservation_reminder_count' => ($applicant->reservation_reminder_count ?? 0) + 1,
                            'updated_at' => now()
                        ]);

                    $this->info("✅ Sent reservation reminder to: {$applicantName} ({$applicant->email})");
                    $sentCount++;

                } catch (\Exception $e) {
                    $this->error("❌ Failed to send reminder to {$applicant->email}: " . $e->getMessage());
                    Log::error('Reservation reminder failed', [
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
        $this->info("🎯 {$verb} {$sentCount} reservation reminders");
        
        Log::info('Reservation reminders completed', [
            'total_found' => $applicants->count(),
            'emails_sent' => $sentCount,
            'simulated' => $simulate
        ]);

        return 0;
    }
}
