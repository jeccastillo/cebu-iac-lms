<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\PHPMailerService;
use App\Models\Faculty;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test:dynamic-emails', function () {
    $this->info('Testing Dynamic Department Email Resolution...');
    
    $mailer = new PHPMailerService();
    
    // Test getting emails by role
    $this->info("\n=== Available Roles in Database ===");
    $roles = \App\Models\Role::where('intActive', 1)->get();
    foreach ($roles as $role) {
        $this->line("- {$role->strCode}: {$role->strName}");
    }
    
    $this->info("\n=== Faculty with Roles ===");
    $faculty = Faculty::with('roles')->where('isActive', 1)->whereNotNull('strEmail')->where('strEmail', '!=', '')->get();
    
    foreach ($faculty as $f) {
        $roleNames = $f->roles->pluck('strName')->join(', ');
        $this->line("- {$f->strFirstname} {$f->strLastname} ({$f->strEmail}): {$roleNames}");
    }
    
    $this->info("\n=== Testing Role Email Resolution ===");
    
    // Test each department role
    $testRoles = ['registrar', 'finance', 'admissions'];
    
    foreach ($testRoles as $role) {
        $this->info("\n--- {$role} Department ---");
        
        // Use reflection to call private method
        $reflection = new ReflectionClass($mailer);
        $method = $reflection->getMethod('getEmailsByRole');
        $method->setAccessible(true);
        
        $emails = $method->invoke($mailer, $role);
        
        if (empty($emails)) {
            $this->warn("No emails found for {$role} role");
        } else {
            $this->info("Found " . count($emails) . " email(s):");
            foreach ($emails as $email) {
                $this->line("  • {$email}");
            }
        }
    }
    
    $this->info("\n=== Testing Sample Notification ===");
    
    // Test a sample notification (without actually sending)
    $sampleData = [
        'applicant_name' => 'Test Applicant',
        'application_number' => 'TEST-001',
        'program_name' => 'Bachelor of Science in Computer Science',
        'campus' => 'Makati',
        'payment_date' => date('Y-m-d H:i:s'),
        'academic_term' => 'AY 2025-2026'
    ];
    
    $this->info("Sample notification data:");
    $this->line(json_encode($sampleData, JSON_PRETTY_PRINT));
    
    $this->info("\n✅ Dynamic email resolution testing complete!");
    
})->purpose('Test dynamic department email resolution based on database roles');
