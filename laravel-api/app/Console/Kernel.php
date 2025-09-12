<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run reminder commands every 2 days
        $schedule->command('reminders:inactive-applications')
                 ->twiceDaily(9, 21) // 9 AM and 9 PM
                 ->withoutOverlapping()
                 ->runInBackground();

        $schedule->command('reminders:interviewed-not-reserved')
                 ->twiceDaily(10, 22) // 10 AM and 10 PM  
                 ->withoutOverlapping()
                 ->runInBackground();
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
