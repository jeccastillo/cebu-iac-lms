<?php

namespace App\Mail\Admissions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Admissions\AdmissionStudentInformation;

class SendAcceptanceLetterMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $information;

    public function __construct(AdmissionStudentInformation $information)
    {
        $this->information = $information;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->view('emails.admissions.acceptance_letter')
                    ->from('markanthony.villudo@gmail.com', 'iACADEMY Portal')
                    ->subject('iACADEMY Admissions: Acceptance Letter')
                    ->replyTo('admissions@iacademy.edu.ph', 'iACADEMY Portal');

        if (!empty($this->information->acceptanceAttachments)) {
            foreach ($this->information->acceptanceAttachments as $k => $v) {
                $mail = $mail->attach(
                    \Storage::path('public/acceptance_attachments/' . $v->filename . '.' . $v->filetype),
                    [
                        'as' => $v->orig_filename,
                    ]
                );
            }
        }
    }
}
