<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sms\PaymentDetail;
use Illuminate\Http\Request;
use App\Models\Sms\{StudentInfoStatusLog};
use App\Mail\Admissions\{AdmissionsNotificationEmail,AdmissionsNotificationMakatiMail};

use Mail, App;

class PaynamicsWebhookController extends Controller
{
    //
    public function __construct(PaymentDetail $paymentDetail)
    {
        $this->paymentDetail = $paymentDetail;        
    }

    public function webhook(Request $request)
    {
        //
        $response =  $request->all();
        //send email to requestor, finance and department involve
        $paymentDetails = $this->paymentDetail->where('request_id', $response['request_id'])
                                                     ->first();
        
        if ($paymentDetails) {

            if ($response['response_message'] == 'Transaction Successful') {
                //transaction is approved.
                $this->paymentDetail->sendEmailAfterPayment($paymentDetails);

                //send email then update status
                $paymentDetails->status = 'Paid';
                $paymentDetails->date_paid = isset($response['date_paid'])?$response['date_paid']:date('F d, Y', strtotime(date('Y-m-d')));
                $paymentDetails->is_sent_email = 1;

                if (App::environment(['local', 'staging'])) 
                    $toEmail = "josephedmundcastillo@gmail.com";
                else{
                    if($paymentDetails->studentInfo->campus == 'Makati')
                        $toEmail = config('emails.admission_makati.email');
                    else
                        $toEmail = config('emails.admission.email');
                }
                //update student info
                if ($paymentDetails->studentInfo->status == 'For Reservation') {
                    $paymentDetails->studentInfo->status = 'Reserved';
                    $paymentDetails->studentInfo->date_reserved = date("Y-m-d");
                                        
                    $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                    'message' =>  "Applicant has paid reservation fee", 
                                    'subject' => "New Reserved Applicant");                
                                        

                    if($paymentDetails->studentInfo->campus == 'Makati'){
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationMakatiMail($mailData)
                        );
                    }else{
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationEmail($mailData)
                        );
                    }
                    
                    StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                    $paymentDetails->studentInfo->update();
                } else if ($paymentDetails->studentInfo->status == 'New') {
                    $paymentDetails->studentInfo->status = 'Waiting For Interview';
                    $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                    'message' =>  "New applicant is waiting for interview link", 
                                    'subject' => "Waiting for Interview");                                    

                    if($paymentDetails->studentInfo->campus == 'Makati'){
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationMakatiMail($mailData)
                        );
                    }else{
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationEmail($mailData)
                        );
                    }

                    StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                    $paymentDetails->studentInfo->update();
                }

            } elseif ($response['response_message'] == 'Transaction Expired') {
                //transaction is expired.
                $paymentDetails->status = 'expired';
                $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
                //send email to student
                // This request has expired. Pleare re-process your order should you wish to pursue with your request
                $this->paymentDetail->sendEmailExpired($paymentDetails);

                StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', 'Transaction Expired');
                $paymentDetails->studentInfo->update();
            }  else {
                //transaction is expired.
                $paymentDetails->status = $response['response_message'];
            }
            $data['success'] = true;
            $data['message'] = 'Sucessfully updated the status';          

            $paymentDetails->remarks = "Paynamics";
            $paymentDetails->response_message = $response['response_message'];
            $paymentDetails->response_advise = $response['response_advise'];
            $paymentDetails->update();
        } else {
            $data['success'] = false;
            $data['message'] = 'Request ID not found.';
        }

        return response()->json($data, 200);
    }

    public function webhook_bdo(Request $request)
    {
        //
        $response =  $request->all();        
        
        //send email to requestor, finance and department involve
         $paymentDetails = $this->paymentDetail->where('request_id', $response['req_reference_number'])
                            ->first();
        
        if ($paymentDetails) {

            if ($response['decision'] == 'ACCEPT') {
                // //transaction is approved.
                //Modify for BDO Email Success
                $this->paymentDetail->sendEmailAfterPayment($paymentDetails);

                // //send email then update status
                $paymentDetails->status = 'Paid';
                $paymentDetails->date_paid = isset($response['date_paid'])?$response['date_paid']:date('F d, Y', strtotime(date('Y-m-d')));
                $paymentDetails->is_sent_email = 1;

                if (App::environment(['local', 'staging'])) 
                    $toEmail = "josephedmundcastillo@gmail.com";
                else{
                    if($paymentDetails->studentInfo->campus == 'Makati')
                        $toEmail = config('emails.admission_makati.email');
                    else
                        $toEmail = config('emails.admission.email');
                }
                //update student info
                if ($paymentDetails->studentInfo->status == 'For Reservation') {
                    $paymentDetails->studentInfo->status = 'Reserved';
                    $paymentDetails->studentInfo->date_reserved = date("Y-m-d");
                                        
                    $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                    'message' =>  "Applicant has paid reservation fee", 
                                    'subject' => "New Reserved Applicant");                
                                        

                    if($paymentDetails->studentInfo->campus == 'Makati'){
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationMakatiMail($mailData)
                        );
                    }else{
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationEmail($mailData)
                        );
                    }
                    
                    StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                    $paymentDetails->studentInfo->update();
                } else if ($paymentDetails->studentInfo->status == 'New') {
                    $paymentDetails->studentInfo->status = 'Waiting For Interview';
                    $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                    'message' =>  "New applicant is waiting for interview link", 
                                    'subject' => "Waiting for Interview");                                    

                    if($paymentDetails->studentInfo->campus == 'Makati'){
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationMakatiMail($mailData)
                        );
                    }else{
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationEmail($mailData)
                        );
                    }

                    StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                    $paymentDetails->studentInfo->update();
                }                

            } elseif ($response['decision'] == 'DECLINE') {
                //transaction is expired.
                $paymentDetails->status = 'DECLINED';
                $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
                //send email to student
                // Send Email that request has been declined
               // $this->paymentDetail->sendEmailExpired($paymentDetails);

                StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', 'Transaction Expired');
                $paymentDetails->studentInfo->update();
            }  else {
                //transaction is expired.
                $paymentDetails->status = $response['decision'];
            }
            $data['success'] = true;            
            if(isset($response['message']))
                $data['message'] = $response['message'];
            else
                $data['message'] = "Successful Transaction";
            
            $paymentDetails->remarks = "BDO Pay";
            $paymentDetails->response_message = $data['message'];
            $paymentDetails->response_advise = $data['message'];            
            $paymentDetails->update();

            if ($response['decision'] == 'ACCEPT'){
                if($paymentDetails->studentInfo->campus == 'Makati')
                    return redirect('https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/success');
                else
                    return redirect('https://cebu.iacademy.edu.ph/site/bdo_redirect_url/success');
            }
        } else {
            $data['success'] = false;
            $data['message'] = 'Request ID not found.';
        }
        
        echo "<h3 style='text-align:center;'>".$data['message']."</h3>";
        
    }

    public function webhook_maya(Request $request)
    {
        $response = $request->all();
        
        $paymentDetails = $this->paymentDetail->where('request_id', $response['requestReferenceNumber'])->first();

        if($paymentDetails){
            if($response['status'] == 'PAYMENT_SUCCESS'){// //send email then update status
                
                $paymentDetails->status = 'Paid';
                $paymentDetails->date_paid = isset($response['date_paid'])?$response['date_paid']:date('F d, Y', strtotime(date('Y-m-d')));
                $paymentDetails->is_sent_email = 1;

                $this->paymentDetail->sendEmailAfterPayment($paymentDetails);

                if (App::environment(['local', 'staging'])) 
                    $toEmail = "rms@iacademy.edu.ph";
                else{
                    if($paymentDetails->studentInfo->campus == 'Makati')
                        $toEmail = config('emails.admission_makati.email');
                    else
                        $toEmail = config('emails.admission.email');
                }
                //update student info
                if ($paymentDetails->studentInfo->status == 'For Reservation') {
                    $paymentDetails->studentInfo->status = 'Reserved';
                    $paymentDetails->studentInfo->date_reserved = date("Y-m-d");
                                        
                    $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                    'message' =>  "Applicant has paid reservation fee", 
                                    'subject' => "New Reserved Applicant");                
                                        

                    if($paymentDetails->studentInfo->campus == 'Makati'){
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationMakatiMail($mailData)
                        );
                    }else{
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationEmail($mailData)
                        );
                    }
                    
                    StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                    $paymentDetails->studentInfo->update();
                } else if ($paymentDetails->studentInfo->status == 'New') {
                    $paymentDetails->studentInfo->status = 'Waiting For Interview';
                    $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                    'message' =>  "New applicant is waiting for interview link", 
                                    'subject' => "Waiting for Interview");                                    

                    if($paymentDetails->studentInfo->campus == 'Makati'){
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationMakatiMail($mailData)
                        );
                    }else{
                        Mail::to($toEmail)->send(
                            new AdmissionsNotificationEmail($mailData)
                        );
                    }

                    StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                    $paymentDetails->studentInfo->update();
                }

                $paymentDetails->remarks = "Maya Pay";                                
                $paymentDetails->update();
                
                $data['success'] = true;
                $data['response'] = $response;
            }
            elseif($response['status'] == 'PAYMENT_FAILED'){
            
                $paymentDetails->status = 'Failed';
                $paymentDetails->is_sent_email = 1;
                $paymentDetails->remarks = "Maya Pay";                                
                $paymentDetails->update();
    
                $data['success'] = false;
                $data['message'] = 'Payment Failed.';
    
            }
            elseif($response['status'] == 'PAYMENT_EXPIRED'){
                $paymentDetails->status = 'Expired';
                $paymentDetails->is_sent_email = 1;
                $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
                $paymentDetails->remarks = "Maya Pay";                                
                $paymentDetails->update();
    
                $data['success'] = false;
                $data['message'] = 'Payment Expired.';
            }
        }
        else{
            $data['success'] = false;
            $data['message'] = 'Request ID not found.';
        }
        

        //return redirect('unity/student_tuition_payment/' . $response['slug']);
        return response()->json($data, 200);
    }

    public function webhook_maxxpayment(Request $request)
    {
        $response = $request->all();
        $response = json_decode($response['sc_values'], true);
        
        $paymentDetails = $this->paymentDetail->where('request_id', $response['SC_REF'])->first();
        
        if(isset($response['SC_STATUS'])){
            if($paymentDetails){
                if($response['SC_STATUS'] == 'approved'){// //send email then update status
                    
                    $paymentDetails->status = 'Paid';
                    $paymentDetails->date_paid = isset($response['date_paid'])?$response['date_paid']:date('F d, Y', strtotime(date('Y-m-d')));
                    $paymentDetails->is_sent_email = 1;

                    $this->paymentDetail->sendEmailAfterPayment($paymentDetails);

                    if (App::environment(['local', 'staging'])) 
                        $toEmail = "rms@iacademy.edu.ph";
                    else{
                        if($paymentDetails->studentInfo->campus == 'Makati')
                            $toEmail = config('emails.admission_makati.email');
                        else
                            $toEmail = config('emails.admission.email');
                    }
                    //update student info
                    if ($paymentDetails->studentInfo->status == 'For Reservation') {
                        $paymentDetails->studentInfo->status = 'Reserved';
                        $paymentDetails->studentInfo->date_reserved = date("Y-m-d");
                                            
                        $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                        'message' =>  "Applicant has paid reservation fee", 
                                        'subject' => "New Reserved Applicant");                
                                            

                        if($paymentDetails->studentInfo->campus == 'Makati'){
                            Mail::to($toEmail)->send(
                                new AdmissionsNotificationMakatiMail($mailData)
                            );
                        }else{
                            Mail::to($toEmail)->send(
                                new AdmissionsNotificationEmail($mailData)
                            );
                        }
                        
                        StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                        $paymentDetails->studentInfo->update();
                        
                    } else if ($paymentDetails->studentInfo->status == 'New') {
                        $paymentDetails->studentInfo->status = 'Waiting For Interview';
                        $mailData = (object) array( 'student' => $paymentDetails->studentInfo, 
                                        'message' =>  "New applicant is waiting for interview link", 
                                        'subject' => "Waiting for Interview");                                    

                        if($paymentDetails->studentInfo->campus == 'Makati'){
                            Mail::to($toEmail)->send(
                                new AdmissionsNotificationMakatiMail($mailData)
                            );
                        }else{
                            Mail::to($toEmail)->send(
                                new AdmissionsNotificationEmail($mailData)
                            );
                        }

                        StudentInfoStatusLog::storeLogs($paymentDetails->studentInfo->id, $paymentDetails->studentInfo->status, '', '');
                        $paymentDetails->studentInfo->update();
                    }

                    $paymentDetails->remarks = "BDO installment";                                
                    $paymentDetails->update();

                    if($paymentDetails->studentInfo->campus == 'Makati')
                        return redirect('https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/success');
                    else
                        return redirect('https://cebu.iacademy.edu.ph/site/bdo_redirect_url/success');

                }
                elseif($response['SC_STATUS'] == 'declined' || $response['SC_STATUS'] == 'declined2'){
                
                    $paymentDetails->status = 'Failed';
                    $paymentDetails->is_sent_email = 1;
                    $paymentDetails->remarks = "BDO installment";                                
                    $paymentDetails->update();
        
                    if($paymentDetails->studentInfo->campus == 'Makati')
                        return redirect('https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/failure');
                    else
                        return redirect('https://cebu.iacademy.edu.ph/site/bdo_redirect_url/failure');    
                }
                elseif($response['SC_STATUS'] == 'down'){
                    $paymentDetails->status = 'Expired';
                    $paymentDetails->is_sent_email = 1;
                    $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
                    $paymentDetails->remarks = "BDO installment";                                
                    $paymentDetails->update();
        
                    if($paymentDetails->studentInfo->campus == 'Makati')
                        return redirect('https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/cancel');
                    else
                        return redirect('https://cebu.iacademy.edu.ph/site/bdo_redirect_url/cancel');  
                }
            }
            else{
                $data['success'] = false;
                $data['message'] = 'Request ID not found.';
            }
        }else{
            $paymentDetails->status = 'Cancelled';
            $paymentDetails->is_sent_email = 1;
            $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
            $paymentDetails->remarks = "BDO installment";                                
            $paymentDetails->update();
            
            if($paymentDetails->studentInfo->campus == 'Makati')
                return redirect('https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/cancel');
            else
                return redirect('https://cebu.iacademy.edu.ph/site/bdo_redirect_url/cancel');  
        }

        if($paymentDetails->studentInfo->campus == 'Makati')
            return redirect('https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/failure');
        else
            return redirect('https://cebu.iacademy.edu.ph/site/bdo_redirect_url/failure'); 
    }
}
