<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerService
{
    public function sendMail($to, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "ssl";

            $mail->Username   = "smsv2testmailer@gmail.com";
            $mail->Password   = "urpnqmokhaxxigxb";
            $mail->Port       = 465;

            $mail->setFrom("smsv2testmailer@gmail.com", "SMS V2 Test Mailer");
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            return "Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
