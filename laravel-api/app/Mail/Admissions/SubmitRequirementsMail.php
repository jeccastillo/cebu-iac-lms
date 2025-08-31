<?php

namespace App\Mail\Admissions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubmitRequirementsMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $information;
    public $updatedFields;

    public function __construct($information, $updatedFields)
    {
        $this->information = $information;
        $this->updatedFields = $updatedFields;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admissions.submit_requirements')
                    ->from('markanthony.villudo@gmail.com', 'iACADEMY Portal')
                    ->subject('iACADEMY Admissions: A new requirement has been submitted by an applicant - ' . $this->information->first_name . ' ' . $this->information->last_name)
                    ->replyTo('no-reply@iacademy.edu.ph', 'iACADEMY Portal');
    }
}
