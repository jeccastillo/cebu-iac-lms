<?php

namespace App\Mail\Admissions;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RejectedApplicationMail extends Mailable
{
    use SerializesModels;

    /**
     * Applicant full name (optional).
     */
    public ?string $applicantName;

    /**
     * Optional reason for failing/rejection.
     */
    public ?string $reason;

    /**
     * Create a new message instance.
     *
     * @param string|null $applicantName
     * @param string|null $reason
     */
    public function __construct(?string $applicantName = null, ?string $reason = null)
    {
        $this->applicantName = $applicantName;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'iACADEMY Admissions: Application Result';

        return $this->view('emails.admissions.rejected_application')
            ->with([
                'name' => $this->applicantName,
                'reason' => $this->reason,
            ])
            ->from('markanthony.villudo@gmail.com', 'iACADEMY Portal')
            ->subject($subject);
    }
}
