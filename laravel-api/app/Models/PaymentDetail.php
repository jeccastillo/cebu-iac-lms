<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
// use App\Models\PaymentOrderItem; // Class doesn't exist
use App\Models\Admissions\AdmissionStudentInformation;
// use App\Http\Resources\Website\WorkShopResource; // Class doesn't exist
// use App\Mail\Registrar\RegistrationConfirmationMakatiMail; // Class doesn't exist
// use App\Models\Website\WorkShop; // Class doesn't exist
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class PaymentDetail extends Model
{
    use HasFactory, SoftDeletes;

    public function scopeFilterByField($query, $field, $searchData)
    {
        if ($field && $searchData) {
            if ($field == 'id') {
                $searchData = str_replace('000000', '', $searchData);
                return $query->where($field, 'like', '%' . $searchData . '%');
            }
            return $query->where($field, 'like', '%' . $searchData . '%');
        }
    }

    public function scopeOrderByField($query, $field, $orderBy)
    {
        if ($field && $orderBy) {
            //if field is status
            return $query->orderBy($field, $orderBy);
        } else {
            return $query->orderBy('created_at', 'DESC');
        }
    }

    public function scopeStatus($query, $status)
    {
        if ($status !== "all") {
            //if field is status
            return $query->where('status', $status);
        }
    }

    public function scopeDepartment($query, $departmentId)
    {
        if ($departmentId) {
            return $query->whereHas('orders', function ($query) use ($departmentId) {
                        $query->whereHas('item', function ($query) use ($departmentId) {
                            $query->where('department_id', $departmentId);
                        });
            });
        }
    }

    public function studentInfo()
    {
        return $this->belongsTo(User::class, 'student_information_id', 'intID');
    }
    public function mode()
    {
        return $this->hasOne(PaymentMode::class, 'id', 'mode_of_payment_id');
    }

    public function orders()
    {
        // return $this->hasMany(PaymentOrderItem::class, 'payment_detail_id', 'id'); // PaymentOrderItem class doesn't exist
        return collect([]); // Return empty collection for now
    }

    public function sendEmailAfterPayment($paymentDetails)
    {

        //template requestor, finance and dept head
        // $financeEmails = config('emails.finance.email');
        $toEmail = $paymentDetails->personal_email;
        $toName = @$paymentDetails->first_name . ' ' . @$paymentDetails->last_name;
        $subjectData = 'iACADEMY Finance: Online Payment Confirmation - ' . $toName;

        if (App::environment(['local', 'staging'])) {
            $logo = "http://103.225.39.201:8081/storage/isign/1597359296_Email_header.png";
            $toEmail = 'portal@iacademy.edu.ph';
        } else {
            $logo = "https://employeeportal.iacademy.edu.ph/storage/isign/1597359296_Email_header.png";
            $toEmail = $paymentDetails->email_address;
        }

        $toEmail = $paymentDetails->email_address;

        $data = array("payment" => $paymentDetails, 'logo' => $logo);
        
        // $workShops = Workshop::get(); // Workshop class doesn't exist
        // $workShops = WorkShopResource::collection($workShops); // WorkShopResource class doesn't exist
        $workShops = []; // Default empty array for now

        if($paymentDetails->student_campus == 'Makati'){
            Mail::send('emails.admissions.makati.notify_requestor_success_payment_makati', $data, function ($message) use ($toName, $toEmail, $subjectData) {
                $message->to($toEmail, $toName)
                        ->subject($subjectData)
                        // ->bcc(['creditcardonline@iacademy.edu.ph'])
                        ->replyTo('creditcardonline@iacademy.edu.ph');
                $message->from('smsalerts@iacademy.edu.ph', 'iACADEMY Portal');
            });
            if($paymentDetails->studentInfo)
                if($paymentDetails->studentInfo->status == 'For Reservation'){
                    // Mail::to($paymentDetails->studentInfo->email)->send(
                    //     new RegistrationConfirmationMakatiMail($paymentDetails->studentInfo,$workShops)
                    // ); // RegistrationConfirmationMakatiMail class doesn't exist
                }

        }else{
            Mail::send('emails.admissions.notify_requestor_success_payment', $data, function ($message) use ($toName, $toEmail, $subjectData) {
                $message->to($toEmail, $toName)
                        ->subject($subjectData)
                        ->bcc(['financecebu@iacademy.edu.ph'])
                        ->replyTo('financecebu@iacademy.edu.ph');
                $message->from('smsalertscebu@iacademy.edu.ph', 'iACADEMY Cebu SMS');
            });
        }
    }

    public function sendEmailExpired($paymentDetails)
    {
        //template requestor, finance and dept head
        $toEmail = $paymentDetails->personal_email;
        $toName = @$paymentDetails->first_name . ' ' . @$paymentDetails->last_name;
        $subjectData = 'iACADEMY Finance: Online Payment Link Expiration - ' . $toName;

        if (App::environment(['local', 'staging'])) {
            $logo = "http://103.225.39.201:8081/storage/isign/1597359296_Email_header.png";
            $toEmail = 'portal@iacademy.edu.ph';
        } else {
            $logo = "https://employeeportal.iacademy.edu.ph/storage/isign/1597359296_Email_header.png";
            $toEmail = $paymentDetails->email_address;
        }

        $toEmail = $paymentDetails->email_address;

        $user = AdmissionStudentInformation::find($paymentDetails->student_information_id);

        switch($paymentDetails->description){
            case "Application Payment":
                $type = 'site/admissions_student_payment/';
                break;
            case "Reservation Payment":
                $type = 'site/admissions_student_payment_reservation/';
                break;
            default:
                $type = 'unity/student_tuition_payment/';
                break;
        }
        $slug = @$user->slug;
        $data = array("payment" => $paymentDetails, 'logo' => $logo, 'url'=> 'https://cebu.iacademy.edu.ph/'.$type.$slug);

        if($paymentDetails->student_campus == 'Makati'){
            Mail::send('emails.finance.notify_expired_transaction', $data, function ($message) use ($toName, $toEmail, $subjectData) {
                $message->to($toEmail, $toName)
                        ->subject($subjectData)
                        ->replyTo('creditcardonline@iacademy.edu.ph');
                $message->from('smsalerts@iacademy.edu.ph', 'iACADEMY Portal');
            });
        }else{
            Mail::send('emails.finance.notify_expired_transaction', $data, function ($message) use ($toName, $toEmail, $subjectData) {
                $message->to($toEmail, $toName)
                        ->subject($subjectData)
                        ->replyTo('financecebu@iacademy.edu.ph');
                $message->from('smsalertscebu@iacademy.edu.ph', 'iACADEMY Cebu SMS');
            });
        }
    }

    public function sendEmailStudent($paymentDetails)
    {
        //template requestor, finance and dept head
        $toEmail = $paymentDetails->personal_email;
        $toName = @$paymentDetails->first_name . ' ' . @$paymentDetails->last_name;
        $subjectData = 'iACADEMY Finance: Online Payment Instructions';

        if (App::environment(['local', 'staging'])) {
            $logo = "http://103.225.39.201:8081/storage/isign/1597359296_Email_header.png";
            $toEmail = 'portal@iacademy.edu.ph';
        } else {
            $logo = "https://portalv2.iacademy.edu.ph/storage/isign/1597359296_Email_header.png";
            $toEmail = $paymentDetails->email_address;
        }

        $toEmail = $paymentDetails->email_address;

        $data = array("payment" => $paymentDetails, 'logo' => $logo);

        if($paymentDetails->student_campus == 'Makati'){
            Mail::send('emails.finance.notify_requestor_nonbank_reference', $data, function ($message) use ($toName, $toEmail, $subjectData) {
                $message->to($toEmail, $toName)
                        ->subject($subjectData)
                        ->replyTo('creditcardonline@iacademy.edu.ph');
                $message->from('inquire@iacademy.edu.ph', 'iACADEMY Portal');
            });
        }else{
            Mail::send('emails.finance.notify_requestor_nonbank_reference', $data, function ($message) use ($toName, $toEmail, $subjectData) {
                $message->to($toEmail, $toName)
                        ->subject($subjectData)
                        ->replyTo('financecebu@iacademy.edu.ph');
                $message->from('inquirecebu@iacademy.edu.ph', 'iACADEMY Cebu SMS');
            });
        }
    }

}
