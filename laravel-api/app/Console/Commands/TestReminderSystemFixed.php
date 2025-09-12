<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestReminderSystemFixed extends Command
{
    protected $signature = 'reminders:test-system';
    protected $description = 'Test the corrected reminder system using tb_mas_applicant_data';

    public function handle()
    {
        $this->info('🧪 Testing Corrected Reminder System');
        $this->info('=====================================');
        
        // Test 1: Check if tb_mas_applicant_data table exists
        if (DB::getSchemaBuilder()->hasTable('tb_mas_applicant_data')) {
            $this->info('✅ tb_mas_applicant_data table exists');
            
            // Check if reminder columns exist
            $hasReminderCols = DB::getSchemaBuilder()->hasColumns('tb_mas_applicant_data', [
                'last_inactive_reminder_sent',
                'last_reservation_reminder_sent'
            ]);
            
            if ($hasReminderCols) {
                $this->info('✅ Reminder tracking columns exist');
            } else {
                $this->warn('⚠️  Reminder tracking columns missing. Run: php artisan migrate');
            }
            
            // Count applicants by status
            $statuses = DB::table('tb_mas_applicant_data')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();
                
            $this->info('📊 Applicant Status Summary:');
            foreach ($statuses as $status) {
                $this->info("   {$status->status}: {$status->count}");
            }
            
        } else {
            $this->error('❌ tb_mas_applicant_data table not found');
        }
        
        // Test 2: Check commands exist
        $this->info('');
        $this->info('🔧 Testing Commands:');
        
        try {
            $this->call('reminders:inactive-applicants', ['--simulate' => true, '--limit' => 1]);
            $this->info('✅ reminders:inactive-applicants command works');
        } catch (\Exception $e) {
            $this->error('❌ reminders:inactive-applicants failed: ' . $e->getMessage());
        }
        
        try {
            $this->call('reminders:reservation-needed', ['--simulate' => true, '--limit' => 1]);
            $this->info('✅ reminders:reservation-needed command works');
        } catch (\Exception $e) {
            $this->error('❌ reminders:reservation-needed failed: ' . $e->getMessage());
        }
        
        $this->info('');
        $this->info('🎯 Next Steps:');
        $this->info('1. Run: php artisan migrate');
        $this->info('2. Test: php artisan reminders:inactive-applicants --simulate --limit=5');
        $this->info('3. Test: php artisan reminders:reservation-needed --simulate --limit=5');
        $this->info('4. Set up CRON: * * * * * cd /path/to/laravel && php artisan schedule:run');
        
        return 0;
    }
}
