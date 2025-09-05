<?php

namespace App\Mail\Admissions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public $date_exam;

    public function __construct($user, $date_exam)
    {
        $this->user = $user;
        $this->date_exam = $date_exam;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admissions.application_submitted')
                    ->from('markanthony.villudo@gmail.com', 'iACADEMY Portal')
                    ->subject('Welcome from iACADEMY')
                    ->replyTo('no-reply@iacademy.edu.ph', 'iACADEMY Portal');
    }
}
