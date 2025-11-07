<?php

namespace App\Exports;

use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ApplicantImportTemplateExport
 *
 * Generates an XLSX template for applicant data import based on tb_mas_applicant_data columns.
 */
class ApplicantImportTemplateExport
{
    /**
     * Build and return the template Spreadsheet instance.
     */
    public function build(): Spreadsheet
    {
        $headers = $this->buildTemplateColumns();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('applicants');

        // Header row
        $colIdx = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($colIdx, 1, $h);
            // Bold header + auto width
            $sheet->getStyleByColumnAndRow($colIdx, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
            $colIdx++;
        }

        // Sample data row
        $sampleData = $this->getSampleData();
        $rowIdx = 2;
        $colIdx = 1;
        foreach ($sampleData as $value) {
            $sheet->setCellValueByColumnAndRow($colIdx, $rowIdx, $value);
            $colIdx++;
        }

        // Optional: add a notes sheet
        try {
            $notes = $spreadsheet->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $notes->setCellValue('A2', 'Fill in the applicant data according to the column headers.');
            $notes->setCellValue('A3', 'Required fields: first_name, last_name, email, date_of_birth.');
            $notes->setCellValue('A4', 'Date of birth format: YYYY-MM-DD');
            $notes->setCellValue('A5', 'Gender options: Male, Female, Other');
            $notes->setCellValue('A6', 'Campus must match existing campus names.');
        } catch (\Throwable $e) {
            // ignore if sheet creation fails
        }

        // Set the applicants sheet as active
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildTemplateColumns(): array
    {
        $cols = [];
        try {
            if (Schema::hasTable('tb_mas_applicant_data')) {
                $cols = Schema::getColumnListing('tb_mas_applicant_data');
            }
        } catch (\Throwable $e) {
            // Fallback to known columns from application form
            $cols = [
                'first_name', 'middle_name', 'last_name', 'suffix', 'email', 'mobile_number',
                'date_of_birth', 'gender', 'address', 'city', 'state_province', 'country', 'zip_code',
                'program_type', 'high_school', 'high_school_address', 'senior_high_school',
                'senior_high_school_address', 'campus', 'student_type', 'track', 'strand',
                'citizenship', 'religion', 'civil_status', 'father_name', 'father_occupation',
                'mother_name', 'mother_occupation', 'guardian_name', 'guardian_contact',
                'emergency_contact_name', 'emergency_contact_number', 'health_conditions',
                'medications', 'allergies', 'awareness_facebook', 'awareness_website',
                'awareness_referral', 'awareness_name_of_referee', 'awareness_others',
                'awareness_others_specify', 'privacy_policy_agreed', 'syid', 'status'
            ];
        }

        // Exclude system columns
        $exclude = ['id', 'user_id', 'created_at', 'updated_at'];
        $headers = array_filter($cols, function($col) use ($exclude) {
            return !in_array($col, $exclude, true);
        });

        // Human-readable headers
        $headerMap = [
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'suffix' => 'Suffix',
            'email' => 'Email',
            'mobile_number' => 'Mobile Number',
            'date_of_birth' => 'Date of Birth (YYYY-MM-DD)',
            'gender' => 'Gender',
            'address' => 'Address',
            'city' => 'City',
            'state_province' => 'State/Province',
            'country' => 'Country',
            'zip_code' => 'Zip Code',
            'program_type' => 'Program/Type',
            'high_school' => 'High School',
            'high_school_address' => 'High School Address',
            'senior_high_school' => 'Senior High School',
            'senior_high_school_address' => 'Senior High School Address',
            'campus' => 'Campus',
            'student_type' => 'Student Type',
            'track' => 'Track',
            'strand' => 'Strand',
            'citizenship' => 'Citizenship',
            'religion' => 'Religion',
            'civil_status' => 'Civil Status',
            'father_name' => 'Father Name',
            'father_occupation' => 'Father Occupation',
            'mother_name' => 'Mother Name',
            'mother_occupation' => 'Mother Occupation',
            'guardian_name' => 'Guardian Name',
            'guardian_contact' => 'Guardian Contact',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_number' => 'Emergency Contact Number',
            'health_conditions' => 'Health Conditions',
            'medications' => 'Medications',
            'allergies' => 'Allergies',
            'awareness_facebook' => 'Awareness - Facebook',
            'awareness_website' => 'Awareness - Website',
            'awareness_referral' => 'Awareness - Referral',
            'awareness_name_of_referee' => 'Awareness - Name of Referee',
            'awareness_others' => 'Awareness - Others',
            'awareness_others_specify' => 'Awareness - Others Specify',
            'privacy_policy_agreed' => 'Privacy Policy Agreed',
            'syid' => 'School Year ID',
            'status' => 'Status'
        ];

        $readableHeaders = [];
        foreach ($headers as $col) {
            $readableHeaders[] = $headerMap[$col] ?? $col;
        }

        return $readableHeaders;
    }

    private function getSampleData(): array
    {
        return [
            'John', // First Name
            'Doe', // Middle Name
            'Smith', // Last Name
            '', // Suffix
            'john.smith@example.com', // Email
            '+1234567890', // Mobile Number
            '2000-01-01', // Date of Birth
            'Male', // Gender
            '123 Main St', // Address
            'City', // City
            'State', // State/Province
            'Country', // Country
            '12345', // Zip Code
            'Bachelor of Science in Computer Science', // Program/Type
            'Sample High School', // High School
            '456 School Ave', // High School Address
            'Sample Senior High', // Senior High School
            '789 Senior St', // Senior High School Address
            'Main Campus', // Campus
            'Regular', // Student Type
            'Academic', // Track
            'STEM', // Strand
            'Filipino', // Citizenship
            'Catholic', // Religion
            'Single', // Civil Status
            'John Smith Sr.', // Father Name
            'Engineer', // Father Occupation
            'Jane Smith', // Mother Name
            'Teacher', // Mother Occupation
            '', // Guardian Name
            '', // Guardian Contact
            'Emergency Contact', // Emergency Contact Name
            '+1234567891', // Emergency Contact Number
            'None', // Health Conditions
            'None', // Medications
            'None', // Allergies
            '1', // Awareness - Facebook
            '1', // Awareness - Website
            '0', // Awareness - Referral
            '', // Awareness - Name of Referee
            '0', // Awareness - Others
            '', // Awareness - Others Specify
            '1', // Privacy Policy Agreed
            '1', // School Year ID
            'new' // Status
        ];
    }
}
