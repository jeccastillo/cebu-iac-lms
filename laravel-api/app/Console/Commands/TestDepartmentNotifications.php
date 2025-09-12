<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PHPMailerService;
use App\Models\Admissions\AdmissionStudentType;

class TestDepartmentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:department-notifications {type?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test department email notifications with actual database field structure';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PHPMailerService $phpMailerService)
    {
        $type = $this->argument('type');
        $all = $this->option('all');

        if ($all || !$type) {
            $this->info('Testing all department notifications...');
            $this->testAllNotifications($phpMailerService);
        } else {
            $this->info("Testing {$type} notification...");
            $this->testSpecificNotification($phpMailerService, $type);
        }

        return 0;
    }

    private function testAllNotifications(PHPMailerService $phpMailerService)
    {
        $testData = $this->getTestData();

        $notifications = [
            'registrar_reserved' => 'sendRegistrarReservedNotification',
            'finance_enlisted' => 'sendFinanceEnlistedNotification',
            'admissions_application' => 'sendAdmissionsApplicationSubmittedNotification',
            'admissions_requirements' => 'sendAdmissionsAllRequirementsSubmittedNotification',
            'admissions_app_fee' => 'sendAdmissionsApplicationFeePaymentNotification',
            'admissions_reservation_fee' => 'sendAdmissionsReservationFeePaymentNotification',
            'admissions_enrolled' => 'sendAdmissionsApplicantEnrolledNotification'
        ];

        foreach ($notifications as $name => $method) {
            try {
                $this->info("Testing {$name}...");
                $result = $phpMailerService->$method($testData[$name]);
                if ($result === true) {
                    $this->info("✅ {$name} sent successfully");
                } else {
                    $this->error("❌ {$name} failed: " . $result);
                }
            } catch (\Exception $e) {
                $this->error("❌ {$name} failed: " . $e->getMessage());
            }
        }
    }

    private function testSpecificNotification(PHPMailerService $phpMailerService, $type)
    {
        $testData = $this->getTestData();
        $notifications = [
            'registrar_reserved' => 'sendRegistrarReservedNotification',
            'finance_enlisted' => 'sendFinanceEnlistedNotification',
            'admissions_application' => 'sendAdmissionsApplicationSubmittedNotification',
            'admissions_requirements' => 'sendAdmissionsAllRequirementsSubmittedNotification',
            'admissions_app_fee' => 'sendAdmissionsApplicationFeePaymentNotification',
            'admissions_reservation_fee' => 'sendAdmissionsReservationFeePaymentNotification',
            'admissions_enrolled' => 'sendAdmissionsApplicantEnrolledNotification'
        ];

        if (!isset($notifications[$type])) {
            $this->error("Unknown notification type: {$type}");
            $this->info("Available types: " . implode(', ', array_keys($notifications)));
            return;
        }

        try {
            $method = $notifications[$type];
            $result = $phpMailerService->$method($testData[$type]);
            if ($result === true) {
                $this->info("✅ {$type} sent successfully");
            } else {
                $this->error("❌ {$type} failed: " . $result);
            }
        } catch (\Exception $e) {
            $this->error("❌ {$type} failed: " . $e->getMessage());
        }
    }

    private function getTestData()
    {
        // Get a sample program type from the database for realistic testing
        $sampleProgram = AdmissionStudentType::first();
        $programName = $sampleProgram ? $sampleProgram->title : 'Bachelor of Science in Computer Science';
        $programId = $sampleProgram ? $sampleProgram->id : 1;

        return [
            'registrar_reserved' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001',
                'program_name' => $programName,
                'program_id' => $programId,
                'campus' => 'Cebu',
                'payment_date' => date('Y-m-d H:i:s'),
                'academic_term' => 'AY 2024-2025 1st Semester'
            ],
            'finance_enlisted' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001',
                'program_name' => $programName,
                'program_id' => $programId,
                'campus' => 'Cebu',
                'tuition_amount' => '₱85,000.00',
                'payment_deadline' => date('Y-m-d', strtotime('+30 days'))
            ],
            'admissions_application' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001',
                'program_name' => $programName,
                'program_id' => $programId,
                'campus' => 'Cebu',
                'submission_date' => date('Y-m-d H:i:s'),
                'contact_email' => 'test.student@email.com',
                'contact_phone' => '+63 912 345 6789'
            ],
            'admissions_requirements' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001'
            ],
            'admissions_app_fee' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001',
                'payment_amount' => '₱1,500.00',
                'payment_date' => date('Y-m-d H:i:s'),
                'payment_reference' => 'REC-TEST-001',
                'payment_method' => 'Online Payment'
            ],
            'admissions_reservation_fee' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001',
                'program_name' => $programName,
                'program_id' => $programId,
                'payment_amount' => '₱5,000.00',
                'payment_date' => date('Y-m-d H:i:s'),
                'payment_reference' => 'REC-TEST-002',
                'academic_term' => 'AY 2024-2025 1st Semester'
            ],
            'admissions_enrolled' => [
                'applicant_name' => 'Test Student',
                'application_number' => 'TEST-2024-001',
                'student_id' => 'STU-2024-001',
                'program_name' => $programName,
                'program_id' => $programId,
                'academic_year' => '2024-2025',
                'application_date' => date('Y-m-d', strtotime('-30 days'))
            ]
        ];
    }
}
