<?php

namespace App\Services;

use App\Models\Faculty;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerService
{
    public function sendMail($to, $subject, $body)
    {
        // DEBUG: Override all email addresses for debugging
        $originalTo = $to;
        $to = "lancekenjiparce@gmail.com";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "ssl";

            $mail->Username   = "smsv2testmailer@gmail.com";
            $mail->Password   = "urpnqmokhaxxigxb";
            $mail->Port       = 465;

            $mail->setFrom("smsv2testmailer@gmail.com", "iACADEMY School Management System");
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = "[DEBUG - Original: {$originalTo}] " . $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            return "Mailer Error: {$mail->ErrorInfo}";
        }
    }

    /**
     * Get emails for users with specific role
     */
    private function getEmailsByRole($roleCode)
    {
        try {
            return Faculty::whereHas('roles', function ($query) use ($roleCode) {
                $query->where('strCode', $roleCode);
            })->where('isActive', 1)
                ->whereNotNull('strEmail')
                ->where('strEmail', '!=', '')
                ->pluck('strEmail')
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to get emails for role ' . $roleCode . ': ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send application confirmation email with username and confirmation code
     */
    public function sendApplicationConfirmation($applicantEmail, $applicantName, $applicationNumber, $username, $confirmationCode)
    {
        try {
            $data = [
                'applicant_name' => $applicantName,
                'application_number' => $applicationNumber,
                'username' => $username,
                'confirmation_code' => $confirmationCode,
                'applicant_email' => $applicantEmail
            ];

            $body = View::make('emails.admissions.application_confirmation', $data)->render();
            $subject = "iACADEMY Application Submitted - Your Login Credentials";

            return $this->sendMail($applicantEmail, $subject, $body);
        } catch (Exception $e) {
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send interview schedule notification
     */
    public function sendInterviewScheduleNotification($applicantEmail, $applicantName, $interviewDate, $interviewTime)
    {
        try {
            $data = [
                'applicant_name' => $applicantName,
                'interview_date' => $interviewDate,
                'interview_time' => $interviewTime
            ];

            $body = View::make('emails.admissions.interview_schedule', $data)->render();
            $subject = "iACADEMY Interview Schedule Confirmation";

            return $this->sendMail($applicantEmail, $subject, $body);
        } catch (Exception $e) {
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send interview passed notification
     */
    public function sendInterviewPassedNotification($applicantEmail, $applicantName, $remarks = null)
    {
        try {
            $data = [
                'applicant_name' => $applicantName,
                'remarks' => $remarks
            ];

            $body = View::make('emails.admissions.interview_passed', $data)->render();
            $subject = "iACADEMY Interview Result - Congratulations!";

            return $this->sendMail($applicantEmail, $subject, $body);
        } catch (Exception $e) {
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send interview failed notification
     */
    public function sendInterviewFailedNotification($applicantEmail, $applicantName, $reasonForFailing = null)
    {
        try {
            $data = [
                'applicant_name' => $applicantName,
                'reason_for_failing' => $reasonForFailing
            ];

            $body = View::make('emails.admissions.interview_failed', $data)->render();
            $subject = "iACADEMY Interview Result";

            return $this->sendMail($applicantEmail, $subject, $body);
        } catch (Exception $e) {
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to registrar when applicant status is set to 'Reserved'
     */
    public function sendRegistrarReservedNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('registrar');

            if (empty($emails)) {
                Log::warning('No registrar emails found for notification');
                return "Warning: No registrar emails found";
            }

            $body = View::make('emails.departments.registrar_reserved', $data)->render();
            $subject = "New Applicant Reserved - Action Required";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Registrar notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to finance when applicant status is set to 'Enlisted'
     */
    public function sendFinanceEnlistedNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('finance');

            if (empty($emails)) {
                Log::warning('No finance emails found for notification');
                return "Warning: No finance emails found";
            }

            $body = View::make('emails.departments.finance_enlisted', $data)->render();
            $subject = "Applicant Enlisted - Ready to Enroll";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Finance notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to admissions when applicant applies
     */
    public function sendAdmissionsApplicationSubmittedNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('admissions');

            if (empty($emails)) {
                Log::warning('No admissions emails found for notification');
                return "Warning: No admissions emails found";
            }

            $body = View::make('emails.departments.admissions_application_submitted', $data)->render();
            $subject = "New Application Submitted - Review Required";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Admissions application notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to admissions when all requirements are submitted
     */
    public function sendAdmissionsAllRequirementsSubmittedNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('admissions');

            if (empty($emails)) {
                Log::warning('No admissions emails found for notification');
                return "Warning: No admissions emails found";
            }

            $body = View::make('emails.departments.admissions_requirements_complete', $data)->render();
            $subject = "All Requirements Submitted - Final Review Required";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Admissions requirements notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to admissions when application fee is paid
     */
    public function sendAdmissionsApplicationFeePaymentNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('admissions');

            if (empty($emails)) {
                Log::warning('No admissions emails found for notification');
                return "Warning: No admissions emails found";
            }

            $body = View::make('emails.departments.admissions_application_fee_payment', $data)->render();
            $subject = "Application Fee Payment Received";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Admissions application fee notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to admissions when reservation fee is paid
     */
    public function sendAdmissionsReservationFeePaymentNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('admissions');

            if (empty($emails)) {
                Log::warning('No admissions emails found for notification');
                return "Warning: No admissions emails found";
            }

            $body = View::make('emails.departments.admissions_reservation_fee_payment', $data)->render();
            $subject = "Reservation Fee Payment Received";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Admissions reservation fee notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }

    /**
     * Send notification to admissions when applicant is enrolled
     */
    public function sendAdmissionsApplicantEnrolledNotification($data)
    {
        try {
            $emails = $this->getEmailsByRole('admissions');

            if (empty($emails)) {
                Log::warning('No admissions emails found for notification');
                return "Warning: No admissions emails found";
            }

            $body = View::make('emails.departments.admissions_applicant_enrolled', $data)->render();
            $subject = "Applicant Successfully Enrolled";

            $results = [];
            foreach ($emails as $email) {
                $results[] = $this->sendMail($email, $subject, $body);
            }

            return $results;
        } catch (Exception $e) {
            Log::error('Admissions enrolled notification failed: ' . $e->getMessage());
            return "Mailer Error: " . $e->getMessage();
        }
    }
}
