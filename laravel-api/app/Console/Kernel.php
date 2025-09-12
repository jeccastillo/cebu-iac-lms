<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Send inactive applicant reminders every 2 days at 9 AM
        $schedule->command('reminders:inactive-applicants')
                ->dailyAt('09:00')
                ->days([1, 3, 5, 0]) // Monday, Wednesday, Friday, Sunday (every ~2 days)
                ->withoutOverlapping(10) // 10 minute timeout
                ->runInBackground()
                ->sendOutputTo('/var/log/laravel/email-reminders.log')
                ->emailOutputOnFailure('lancekenjiparce@gmail.com')
                ->onSuccess(function () {
                    Log::info('Inactive applicants reminder completed successfully');
                })
                ->onFailure(function () {
                    Log::error('Inactive applicants reminder failed');
                });

        // Send reservation reminders every 2 days at 10 AM
        $schedule->command('reminders:reservation-needed')
                ->dailyAt('10:00') 
                ->days([1, 3, 5, 0]) // Monday, Wednesday, Friday, Sunday (every ~2 days)
                ->withoutOverlapping(10) // 10 minute timeout
                ->runInBackground()
                ->sendOutputTo('/var/log/laravel/email-reminders.log')
                ->emailOutputOnFailure('lancekenjiparce@gmail.com')
                ->onSuccess(function () {
                    Log::info('Reservation reminders completed successfully');
                })
                ->onFailure(function () {
                    Log::error('Reservation reminders failed');
                });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
