<?php

namespace App\Mail\Admissions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Admissions\AdmissionStudentInformation;

class SubmitInformationMail extends Mailable
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
        return $this->view('emails.admissions.submit_information')
                    ->from('markanthony.villudo@gmail.com', 'iACADEMY Portal')
                    ->subject('iACADEMY Admissions: Online Application - ' . $this->information->first_name . ' ' . $this->information->last_name)
                    ->replyTo('admissions@iacademy.edu.ph', 'iACADEMY Portal');
    }
}
