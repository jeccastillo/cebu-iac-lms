<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendPHPMailJob;

class TestPHPMailer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan test:phpmailer
     */
    protected $signature = 'test:phpmailer {email?}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email using PHPMailer via queued job';

    public function handle()
    {
        $to = $this->argument('email') ?? 'lancekenjiparce@gmail.com';

        // simple Blade template render
        $body = view('emails.admissions.application_submitted', [
            'user' => (object)['first_name' => 'Kenji', 'last_name' => 'Parce'],
            'date_exam' => 'September 20, 2025',
            ])->render();

        dispatch(new SendPHPMailJob($to, 'Welcome from iACADEMY', $body));

        $this->info("Queued a PHPMailer job to send test mail to {$to}");
    }
}
