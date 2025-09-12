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
        $schedule->command('reminders:inactive-applications')
                ->dailyAt('09:00')
                ->days([1, 3, 5, 0])
                ->withoutOverlapping(10)
                ->runInBackground()
                ->sendOutputTo('/var/log/laravel/email-reminders.log')
                ->emailOutputOnFailure('lancekenjiparce@gmail.com')
                ->onSuccess(function () {
                    Log::info('Inactive applications reminder completed successfully');
                })
                ->onFailure(function () {
                    Log::error('Inactive applications reminder failed');
                });

        $schedule->command('reminders:interviewed-not-reserved')
                ->dailyAt('10:00') 
                ->days([1, 3, 5, 0])
                ->withoutOverlapping(10)
                ->runInBackground()
                ->sendOutputTo('/var/log/laravel/email-reminders.log')
                ->emailOutputOnFailure('lancekenjiparce@gmail.com')
                ->onSuccess(function () {
                    Log::info('Interviewed not reserved reminder completed successfully');
                })
                ->onFailure(function () {
                    Log::error('Interviewed not reserved reminder failed');
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
