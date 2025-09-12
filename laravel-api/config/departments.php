<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Department Email Addresses
    |--------------------------------------------------------------------------
    |
    | These email addresses are used for sending notifications to different
    | departments when specific events occur in the student information system.
    |
    */

    'registrar' => env('REGISTRAR_EMAIL', 'registrar@iacademy.edu.ph'),
    
    'finance' => env('FINANCE_EMAIL', 'finance@iacademy.edu.ph'),
    
    'admissions' => env('ADMISSIONS_EMAIL', 'admissions@iacademy.edu.ph'),
    
    /*
    |--------------------------------------------------------------------------
    | Department Notification Settings
    |--------------------------------------------------------------------------
    |
    | Control which notifications are enabled for each department.
    |
    */

    'notifications' => [
        'registrar' => [
            'applicant_reserved' => env('REGISTRAR_NOTIFY_RESERVED', true),
        ],
        
        'finance' => [
            'applicant_enlisted' => env('FINANCE_NOTIFY_ENLISTED', true),
        ],
        
        'admissions' => [
            'application_submitted' => env('ADMISSIONS_NOTIFY_APPLICATION', true),
            'requirements_complete' => env('ADMISSIONS_NOTIFY_REQUIREMENTS', true),
            'application_fee_payment' => env('ADMISSIONS_NOTIFY_APP_FEE', true),
            'reservation_fee_payment' => env('ADMISSIONS_NOTIFY_RESERVATION_FEE', true),
            'applicant_enrolled' => env('ADMISSIONS_NOTIFY_ENROLLED', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Template Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for email templates and sending preferences.
    |
    */

    'from_name' => env('DEPT_EMAIL_FROM_NAME', 'iACADEMY Student Information System'),
    'from_email' => env('DEPT_EMAIL_FROM_ADDRESS', 'noreply@iacademy.edu.ph'),
    
    'copy_admin' => env('DEPT_EMAIL_COPY_ADMIN', false),
    'admin_email' => env('DEPT_EMAIL_ADMIN', 'admin@iacademy.edu.ph'),
];
