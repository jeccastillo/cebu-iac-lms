<?php

namespace App\Http\Controllers\Api\Sms;

use App\Http\Controllers\Controller;
use App\Models\Sms\{PaymentDetail, PaymentItem};
use App\Models\Sms\{PaymentMode, PaymentOrderItem};
use App\Models\Sms\AdmissionStudentInformation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Lloricode\Paymaya\Request\Checkout\Amount\AmountDetail;
use Lloricode\Paymaya\Request\Checkout\Amount\Amount;
use Lloricode\Paymaya\Request\Checkout\Buyer\BillingAddress;
use Lloricode\Paymaya\Request\Checkout\Buyer\Buyer;
use Lloricode\Paymaya\Request\Checkout\Buyer\Contact;
use Lloricode\Paymaya\Request\Checkout\Buyer\ShippingAddress;
use Lloricode\Paymaya\Request\Checkout\Checkout;
use Lloricode\Paymaya\Request\Checkout\Item;
use Lloricode\Paymaya\Request\Checkout\MetaData;
use Lloricode\Paymaya\Request\Checkout\RedirectUrl;
use Lloricode\Paymaya\Request\Checkout\TotalAmount;
use Lloricode\Paymaya\PaymayaClient;
use Illuminate\Support\Facades\Config;
use PaymayaSDK;

use DB, App;

class PaymentGatewayController extends Controller
{
    public function __construct(
        PaymentDetail $paymentDetail,
        PaymentItem $item,
        PaymentMode $mode,
        PaymentOrderItem $paymentItem,
        AdmissionStudentInformation $studentInformation
    ) {
        $this->paymentDetail = $paymentDetail;
        $this->item = $item;
        $this->mode = $mode;
        $this->paymentItem = $paymentItem;
        $this->studentInformation = $studentInformation;

    }

    function sign($params,$secretKey) {
        return signData(buildDataToSign($params), $secretKey);
    }
    
    function signData($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }
    
    function buildDataToSign($params) {
            $signedFieldNames = explode(",",$params["signed_field_names"]);
            foreach ($signedFieldNames as $field) {
                $dataToSign[] = $field . "=" . $params[$field];
            }
            return commaSeparate($dataToSign);
    }
    
    function commaSeparate($dataToSign) {
        return implode(",",$dataToSign);
    }

    public function pay(Request $request)
    {
        DB::beginTransaction();
        try {
            $mailingFee = 0;
            $subtotal = $request->total_price_without_charge;
            $total = $request->total_price_with_charge;

            $modePayment = $this->mode->find($request->mode_of_payment_id);
            $chargeDefault = $request->charge;
            $chargeDefault = number_format((float)$chargeDefault, 2, '.', '');

            if ($modePayment && $modePayment->type == 'percentage') {
                //My number is subtotal
                //I want to get $modePayment->charge
                //Convert our percentage value into a decimal.
                $percentInDecimal = $modePayment->charge / 100;
                //Get the result.
                $charge = $percentInDecimal * $subtotal;
                $charge = number_format((float)$charge, 2, '.', '');

                if ($charge < 28) {
                    $charge = 28;
                }
            } else {
                $charge = $chargeDefault;
            }

            if(request('mailing_fee')){
                $mailingFee = request('mailing_fee');
            }

            if ($chargeDefault != $charge) {
                $data['message'] = 'Failed, wrong computations of charges.';
                $data['success'] = false;
                $data['charge'] = 'default' . $chargeDefault . 'backend' . $charge;
                $data['mailing_fee'] = 'frontend' . request('mailing_fee') . 'backend' . $mailingFee;
                $data['subtotal'] = $subtotal + $mailingFee;
                $data['mailing_cost'] = $mailingFee;
                $data['total'] = $total;
                $data['default_charge'] = $chargeDefault;
                DB::commit();
                return response()->json($data, 200);
            } else {
                $charge = $chargeDefault;
            }

            //payment mode + random string
            $requestId = $request->mode_payment_name . '' . substr(uniqid(), 0, 18);

            $student = $this->studentInformation::find($request->student_information_id);
            
            $newPaymentDetails = new $this->paymentDetail();
            $newPaymentDetails->request_id = $requestId;
            $newPaymentDetails->slug = \Str::uuid();
            $newPaymentDetails->description = $request->description;
            $newPaymentDetails->student_information_id = $request->student_information_id;
            $newPaymentDetails->student_number = $request->student_number;
            $newPaymentDetails->first_name = $request->first_name;
            $newPaymentDetails->middle_name = $request->middle_name;
            $newPaymentDetails->last_name = $request->last_name;
            $newPaymentDetails->email_address = $request->email;            
            $newPaymentDetails->mode_of_payment_id = $request->mode_of_payment_id;
            $newPaymentDetails->remarks = $request->remarks;
            $newPaymentDetails->convenience_fee = $modePayment?$modePayment->charge:0;
            $newPaymentDetails->subtotal_order = $subtotal + $mailingFee;
            $newPaymentDetails->total_amount_due = $total + $mailingFee;
            $newPaymentDetails->charges = $total - $subtotal;
            $newPaymentDetails->contact_number = $request->contact_number;
            $newPaymentDetails->name_of_school = @$request->name_of_school;
            $newPaymentDetails->course = @$request->course;
            $newPaymentDetails->sy_reference = @$request->sy_reference;
            $newPaymentDetails->student_campus = $student->campus;
            $newPaymentDetails->ip_address = @$request->ip();
            $newPaymentDetails->save();

            if ($request->order_items) {
                $paynamicsOrders = [];
                foreach ($request->order_items as $orderItem) {
                    $newPaymentItem = new $this->paymentItem();
                    $newPaymentItem->price = $orderItem['price_default'];
                    $newPaymentItem->qty = $orderItem['qty'];
                    $newPaymentItem->item_id = $orderItem['id'];
                    $newPaymentItem->payment_detail_id = $newPaymentDetails->id;
                    $newPaymentItem->term = @$orderItem['term'];
                    $newPaymentItem->academic_year = @$orderItem['academic_year'];
                    $newPaymentItem->save();

                    $paynamicsOrders[] = [
                        "itemname" => $orderItem['title'],
                        "quantity" => $orderItem['qty'],
                        "unitprice" => $orderItem['price_default'],
                        "totalprice" => $orderItem['price_default'] * $orderItem['qty'],
                    ];
                }
            }
            
            if (App::environment(['local', 'staging'])) {
                $merchantid = "000000200223B5713D7D";
                $mkey = "AABF87F484B8ECA1DA52B731E2C55A9B";
                $username = 'iacademy8B7j';
                $password = '8B7jcW9FeJ9Y';
                
                if($request->mode_of_payment_id != 99 && $request->mode_of_payment_id != 100 && $request->mode_of_payment_id != 101)
                    $url = 'https://payin.payserv.net/paygate/transactions/';
                else if($request->mode_of_payment_id == 101)
                    $url = 'https://sandbox.maxxpayment.com/api/mp/?live=0';
                else
                    $url = 'https://secureacceptance.cybersource.com/pay';

                $bdo_acces_key = "6ec0f0930c3b36698d52428a5aed80d1";
            } else {
                if($student->campus == 'Makati'){
                    $merchantid = "000000220821A30BF10B";
                    $mkey = "77466C4E0EC95380E8BBE4A5D9ACA6CD";
                    $username = 'ictacademyRMB6';
                    $password = 'Az5WXjNfbym7';
                    if($request->mode_of_payment_id != 99 && $request->mode_of_payment_id != 100 && $request->mode_of_payment_id != 101)
                        $url = 'https://payin.paynamics.net/paygate/transactions/';
                    else if($request->mode_of_payment_id == 101)
                        $url = 'https://secure.maxxpayment.com/api/mp?live=1';
                    else
                        $url = 'https://secureacceptance.cybersource.com/pay';        
                    
                    $bdo_acces_key = "6ec0f0930c3b36698d52428a5aed80d1";
                }else{
                    $merchantid = "0000002802239213DA0B";
                    $mkey = "B2A17D5EF9A1EBA62F3C9E30D7198921";
                    $username = 'iAcademy15mh';
                    $password = '15mhj3F3DvoJ';
                    
                    if($request->mode_of_payment_id != 99 && $request->mode_of_payment_id != 100 && $request->mode_of_payment_id != 101)
                        $url = 'https://payin.paynamics.net/paygate/transactions/';
                    else if($request->mode_of_payment_id == 101)
                        $url = 'https://secure.maxxpayment.com/api/mp?live=1';
                    else
                        $url = 'https://secureacceptance.cybersource.com/pay';        

                    $bdo_acces_key = "6ec0f0930c3b36698d52428a5aed80d1";
                }
            }
            //IF Paynamics
            if($request->mode_of_payment_id != 99 && $request->mode_of_payment_id != 100 && $request->mode_of_payment_id != 101){
                $requestId = $newPaymentDetails->request_id;
                $notURL = route('webhook');
                $responseURL = "https://cebu.iacademy.edu.ph/site/awesome/";
                $cancelURL = $modePayment->pmethod == 'onlinebanktransfer' ? 'https://iacademy.edu.ph/'.$request->description : route('cancel-payment');
                $pmethod = $modePayment->pmethod;
                $paymentAction = $modePayment->pmethod;
                $pchannel = $modePayment->pchannel;
                $collectionMethod = 'single_pay';
                $amount = $total + $mailingFee;  //charge for payment mode.
                $total = $amount;
                $currency = "PHP";
                $payNotStatus = "1";
                // $payNotChannel = $modePayment->payment_action == 'onlinebanktransfer' ? "1" : "";
                // $payNotChannel = $pmethod == 'onlinebanktransfer' ? '1' : '';
                $payNotChannel = $pchannel == 'ubp_online' ? '1' : '';

                $rawTrx = $merchantid .
                        $requestId .
                        $notURL .
                        $responseURL .
                        $cancelURL .
                        $pmethod .
                        $paymentAction .
                        $collectionMethod .
                        $amount .
                        $currency .
                        $payNotStatus .
                        $payNotChannel .
                        $mkey;

                $signatureTrx = hash("SHA512", $rawTrx);

                $fname = $request->first_name;
                $lname = $request->last_name;
                $mname = $request->middle_name ? $request->middle_name : '';
                $email = $request->email;
                $phone = $request->contact_number;
                $mobile = $request->contact_number;
                $dob = $request->dob;

                $raw = $fname .
                        $lname .
                        $mname .
                        $email .
                        $phone .
                        $mobile .
                        $dob .
                        $mkey;

                $signature = hash("sha512", $raw);


                // var raw = fname + lname + mname + email + phone + mobile + dob + mkey;
                // var signature = CryptoJS.enc.Hex.stringify(CryptoJS.SHA512(raw));

                // rawTrx = merchantid + request_id + notification_url + response_url + cancel_url + pmethod + payment_action + collectionMethod +
                // amount + currency + payment_notification_status + payment_notification_channel + mkey;
                // If any HTTP authentication is needed.                
                $charge = $modePayment->charge;

                // // if($mailingFee){
                //     $subtotal += $mailingFee;
                // }
                if ($modePayment->type == 'percentage') {
                    $charge = round(($modePayment->charge * 100) / $subtotal, 2);
                }
            }
            elseif($request->mode_of_payment_id == 99 ){            
                $charge = 0;
                $pmethod = "bdo_pay";
            }   
            elseif($request->mode_of_payment_id == 100 ){
                $charge = 0;
                $pmethod = "maya_pay";
            } 
            elseif($request->mode_of_payment_id == 101 ){
                $charge = 0;
                $pmethod = "maxx_payment";
            } 

            $requestType = 'POST'; // This can be PUT or POST
            $arrPostData = '';

            if ($pmethod == 'onlinebanktransfer' || $pmethod == 'wallet') {
                $paynamicsOrders[] = [
                    "itemname" => "Fee",
                    "quantity" => 1,
                    "unitprice" => $total - $subtotal - $mailingFee,
                    "totalprice" => $total - $subtotal - $mailingFee
                ];
                
                if($mailingFee > 0){
                    $paynamicsOrders[] = [
                       "itemname" => "Mailing Fee",
                       "quantity" =>  1,
                       "unitprice" => $mailingFee,
                       "totalprice" => $mailingFee
                    ];
                }

                $arrPostData = [
                    "transaction" => [
                        "request_id" => $requestId,
                        "notification_url" => $notURL,
                        "response_url" => $responseURL,
                        "cancel_url" => $cancelURL,
                        "pmethod" => $pmethod,
                        "pchannel" => $pchannel,
                        "payment_action" => $paymentAction,
                        "schedule" => "",
                        "collection_method" => $collectionMethod,
                        "deferred_period" => "",
                        "deferred_time" => "",
                        "dp_balance_info" => "",
                        "amount" => $total,
                        "currency" => $currency,
                        "pay_reference" => "",
                        "payment_notification_status" => $payNotStatus,
                        "payment_notification_channel" => $payNotChannel,
                        "signature" => $signatureTrx,
                    ],
                    "customer_info" => [
                        "fname" => $fname,
                        "lname" => $lname,
                        "mname" => $mname,
                        "email" => $email,
                        "phone" => $phone,
                        "mobile" => $mobile,
                        "dob" => $dob,
                        "signature" => $signature,
                    ],
                    "order_details" => [
                        "orders" => $paynamicsOrders,
                        "subtotalprice" => $total,
                        "shippingprice" => "0.00",
                        "discountamount" => "0.00",
                        "totalorderamount" => $total
                    ]
                ];
                
                // return $arrPostData;
            } elseif ($pmethod == 'nonbank_otc') {
                $todayDateTime = date('m/d/Y h:i:s', strtotime(date('Y/m/d') . ' + 2 days'));
                $amount = $total;

                $rawTrx = $merchantid .
                $requestId .
                $notURL .
                $responseURL .
                $cancelURL .
                $pmethod .
                $paymentAction .
                $collectionMethod .
                $amount .
                $currency .
                $payNotStatus .
                $payNotChannel .
                $mkey;

                $signatureTrx = hash("sha512", $rawTrx);

                $paynamicsOrders[] = [
                   "itemname" => "Fee",
                   "quantity" =>  1,
                   "unitprice" => $charge,
                   "totalprice" => $charge
                ];

                if($mailingFee > 0){
                    $paynamicsOrders[] = [
                       "itemname" => "Mailing Fee",
                       "quantity" =>  1,
                       "unitprice" => $mailingFee,
                       "totalprice" => $mailingFee
                    ];
                }

                $arrPostData = [
                    "transaction" => [
                        "request_id" => $requestId,
                        "notification_url" => $notURL,
                        "response_url" => $responseURL,
                        "cancel_url" => $cancelURL,
                        "pmethod" => $pmethod,
                        "pchannel" => $pchannel,
                        "payment_action" => $paymentAction,
                        "schedule" => "",
                        "collection_method" => $collectionMethod,
                        "deferred_period" => "",
                        "deferred_time" => "",
                        "dp_balance_info" => "",
                        "amount" => $total,
                        "currency" => "PHP",
                        "descriptor_note" => "",
                        "payment_notification_status" => $payNotStatus,
                        "payment_notification_channel" => $payNotChannel,
                        "expiry_limit" => $todayDateTime,
                        "signature" => $signatureTrx,
                    ],
                    "customer_info" => [
                        "fname" => $fname,
                        "lname" => $lname,
                        "mname" => $mname,
                        "email" => $email,
                        "phone" => $phone,
                        "mobile" => $mobile,
                        "dob" => $dob,
                        "signature" => $signature,
                    ],
                    "billing_info" => [
                        "billing_address1" => "",
                        "billing_address2" => "",
                        "billing_city" => "",
                        "billing_state" => "",
                        "billing_country" => "PH",
                        "billing_zip" => ""
                    ],
                    "order_details" => [
                        "orders" => $paynamicsOrders,
                        "subtotalprice" => $total,
                        "shippingprice" => "0.00",
                        "discountamount" => "0.00",
                        "totalorderamount" => $total,
                    ]
                ];
            }
            elseif ($pmethod == 'bdo_pay') {

                $secretKey = "4210f73b76834dc1832bd860d7f6515805e531f9c95144f88bb1a2115a25488eceb5681efafd4eb885421c8e1761e0e47048ba4dda684ddb9df191149fcfa2ab4e87849b0ce74578a19df2ff4976c614836bad6f1575474c9e97cca2ba02ccf2a4eaa0f5c588460cb1b0ca5c2b0096d281fa0ce5c4874a128af062c42240bd1a";                
                $amount = $total;

                $params['access_key'] = $bdo_acces_key;
                $params['profile_id'] = "4AC27C6B-C708-484E-92F5-C2352F335211";
                $params['transaction_uuid'] = uniqid();
                $params['signed_field_names'] = "access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,bill_to_address_line1,bill_to_address_city,bill_to_address_country,bill_to_email,bill_to_surname,bill_to_forename";
                $params['signed_date_time'] = gmdate("Y-m-d\TH:i:s\Z");
                $params['transaction_type'] = "sale";
                $params['reference_number'] = $requestId;
                $params['amount'] = number_format( $amount, 2, '.', '' );
                $params['currency'] = "PHP";
                $params['locale'] = "en"; 
                $params['unsigned_field_names'] = "";
                $params['bill_to_address_line1'] = "iACADEMY Nexxus Yakal St";
                $params['bill_to_address_city'] = "Makati City";
                $params['bill_to_address_country'] = "PH";
                $params['bill_to_email'] = $request->bill_to_email;
                $params['bill_to_surname'] = $request->bill_to_surname;
                $params['bill_to_forename'] = $request->bill_to_forename;
                
                
                
                $signedFieldNames = explode(",",$params["signed_field_names"]);
                foreach ($signedFieldNames as $field) {
                    $dataToSign[] = $field . "=" . $params[$field];
                }
                        
                $dataToSign = implode(",",$dataToSign);
                

                $signature = base64_encode(hash_hmac('sha256', $dataToSign, $secretKey, true));
                $arrPostData = $params;
                $arrPostData['signature'] = $signature;
                
            }
            elseif ($pmethod == 'maya_pay'){ 
                
                $failureUrl = 'cebustaging.iacademy.edu.ph';
                if (\App::environment(['local', 'staging'])) {
                        Config::set('paymaya-sdk.mode', PaymayaClient::ENVIRONMENT_SANDBOX);
                }else{
                    if($student->campus == 'Cebu'){
                        Config::set('paymaya-sdk.keys.secret', 'sk-2tglvJ050xJ3Pcqa9cRfb2sprsmuvZAvWw2HQe1DjAD');
                        Config::set('paymaya-sdk.keys.public', 'pk-bPIZy0vHA5BnMFezVyBg6btpAuHf6KaYp7yppBmHAni');
                        $failureUrl = 'cebu.iacademy.edu.ph';
                    }else if($student->campus == 'Makati'){
                        Config::set('paymaya-sdk.keys.secret', 'sk-n08wtfXteTusHlJssYUNd6aNFVVJmZVHgVCDWRa55N6');
                        Config::set('paymaya-sdk.keys.public', 'pk-VnSJaz0kwzyWjNkAHmPboDR7ny8MwgOKppKRTpTkorr');
                        $failureUrl = 'sms-makati.iacademy.edu.ph';
                    }
                }
                         
                $itemsTotalAmount = 0;
                $phone = $student->contact_number ? $student->contact_number : '';
                $s_email = $student->email ? $student->email : '';
                $contact = (new Contact())->setPhone($phone)
                                          ->setEmail($s_email);

                $user = (new Buyer())->setFirstName($request->first_name)
                                     ->setMiddleName($request->middle_name)
                                     ->setLastName($request->last_name)
                                     ->setContact($contact);
                
                $checkout = (new Checkout())
                    ->setBuyer($user)
                    ->setRedirectUrl(
                       (new RedirectUrl())
                           ->setFailure($failureUrl . '/unity/student_tuition_payment/' . $student->slug)
                   )
                    ->setRequestReferenceNumber($requestId)
                    ->setMetadata(
                        (new MetaData())
                            ->setSMI('smi')
                            ->setSMN('smn')
                            ->setMCI('mci')
                            ->setMPC('mpc')
                            ->setMCO('mco')
                            ->setMST('mst')
                    );

                foreach($paynamicsOrders as $order){
                    $item = new Item();
                    $item->name = $order['itemname'];
                    $item->quantity = $order['quantity'];

                    $itemAmount = $order['unitprice'] * $order['quantity'];
                    $item->totalAmount = (new Amount())
                        ->setValue($order['totalprice']);
                    $itemsTotalAmount += $order['totalprice'];
                    $checkout->addItem($item);
                }

                $checkout->setTotalAmount(
                    (new TotalAmount())
                        ->setValue($itemsTotalAmount)
                        ->setDetails(
                            (new AmountDetail())
                                ->setSubtotal($itemsTotalAmount)                                
                        )
                );

                $checkoutResponse = PaymayaSDK::checkout()->execute($checkout);
            }
            elseif ($pmethod == 'maxx_payment'){
                $mc_tc = 'SC000419';
                $successUrl = $failureUrl = $cancelUrl = '';
                // $successUrl = 'https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/success';
                // $failureUrl = 'https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/failure';
                // $cancelUrl = 'https://sms-makati.iacademy.edu.ph/site/bdo_redirect_url/cancel';
                
                if($newPaymentDetails->student_campus == 'Cebu'){
                    // $successUrl = 'https://cebu.iacademy.edu.ph/site/bdo_redirect_url/success';
                    // $failureUrl = 'https://cebu.iacademy.edu.ph/site/bdo_redirect_url/failure';
                    // $cancelUrl = 'https://cebu.iacademy.edu.ph/site/bdo_redirect_url/cancel';
                    // $successUrl = 'https://cebuapi.iacademy.edu.ph/api/v1/sms/payments/webhook-maxxpayment';
                    // $failureUrl = 'https://cebuapi.iacademy.edu.ph/api/v1/sms/payments/webhook-maxxpayment';
                    // $cancelUrl = 'https://cebuapi.iacademy.edu.ph/api/v1/sms/payments/webhook-maxxpayment';
                }

                $url .=  '&SC_MC=' . $mc_tc . '&SC_AMOUNT=' . $total . '&SC_REF=' . $requestId . '&SC_OPTIONS={"show_paymode":"1,2,3,4","show_payterm":"3,6,9,12,18,24"}&SC_SUCCESSURL=' . 
                            $successUrl . '&SC_FAILURL=' . $failureUrl . '&SC_CANCELURL=' . $cancelUrl . '&SC_CUR_DATA=PHP|1&SC_FREDIRECT=1';

                $mixResponse = $this->executeCurl(array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,                                        
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER  => false,
                    CURLOPT_HTTPHEADER  => array(
                        "X-HTTP-Method-Override: " . $requestType,
                        'Content-Type: application/json', // Only USE this when requesting JSON data
                    ),
                ));

                $mixResponse = str_replace('1|','', $mixResponse);
            }
            

            if ($pmethod == 'bdo_pay') {   
                
                // $postData = urldecode(http_build_query($arrPostData)); // Raw PHP array   

                // $mixResponse = $this->executeCurl(array(
                //     CURLOPT_URL => $url,
                //     CURLOPT_RETURNTRANSFER => true,                                        
                //     CURLOPT_CUSTOMREQUEST => $requestType,
                //     CURLOPT_POSTFIELDS  => $postData,                    
                // ));
                
                $data['success'] = true;
                DB::commit();
                $data['post_data'] = $arrPostData;
                
                
            }else if($pmethod == 'maya_pay'){

                $data['success'] = true;
                DB::commit();
                $data['post_data'] = $checkoutResponse;
            }else if($pmethod == 'maxx_payment'){

                $data['success'] = true;
                DB::commit();
                $data['payment_link'] = $mixResponse;
            }
            else{
                
                $postData = http_build_query($arrPostData); // Raw PHP array   
                $postData = json_encode($arrPostData); // Only USE this when request JSON data.
                
                $mixResponse = $this->executeCurl(array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPGET => true,
                    CURLOPT_VERBOSE => true,
                    CURLOPT_AUTOREFERER => true,
                    CURLOPT_CUSTOMREQUEST => $requestType,
                    CURLOPT_POSTFIELDS  => $postData,
                    CURLOPT_HTTPHEADER  => array(
                        "X-HTTP-Method-Override: " . $requestType,
                        'Content-Type: application/json', // Only USE this when requesting JSON data
                    ),
    
                    // If HTTP authentication is required, use the below lines.
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                    CURLOPT_USERPWD  => $username . ':' . $password
                ));

                // Will dump a beauty json :3
                $responsePaynamics = json_decode($mixResponse, true);
                
                //now update the data based on paynamics response
                $paymentId = $newPaymentDetails->id;
                $paymentDetail = $this->paymentDetail->find($paymentId);

                $paymentDetail->response_message = @$responsePaynamics['response_message'];
                $paymentDetail->response_advise = @$responsePaynamics['response_advise'];
                $paymentDetail->payment_action_info = @$responsePaynamics['payment_action_info'];
                $paymentDetail->response_id = @$responsePaynamics['response_id'];

                // $data['response'] = $responsePaynamics;
                $data['data'] = @$responsePaynamics;

                if ($modePayment->is_nonbank) {
                    $paymentDetail->pay_reference = $responsePaynamics['direct_otc_info'][0]['pay_reference'];
                    $paymentDetail->pay_instructions = $responsePaynamics['direct_otc_info'][0]['pay_instructions'];
                }

                $paymentDetail->update();

                //$data['response'] = $responsePaynamics;

                if ($pmethod == 'onlinebanktransfer' || $pmethod == 'wallet') {
                    $data['response_paynamics'] = $responsePaynamics;
                    $data['success'] = true;
                    // $data['merchantid'] = '000000250621A30BDED2';
                    // $data['mkey'] = 'EFBC7AB78E76574FF18A9C87C46ADD7A';
                    // $data['username'] = 'iacademy6DkF';
                    // $data['password'] = 'j3YdELFVt235';
                    // $data['url'] = $url;
                    $data['notification_url'] = $notURL;
                    $data['response_url'] = $responseURL;
                    $data['cancel_url'] = $cancelURL;
                    $data['payment_link'] = $paymentDetail->payment_action_info;
                } else {
                    if($pchannel != "711_ph")
                        $data['payment_link'] = @$responsePaynamics['direct_otc_info'][0]['pay_reference'];
                    else
                        $data['payment_link'] = $paymentDetail->payment_action_info;
                    
                    $data['message'] = 'Please check your email for the payment instructions.';
                    $data['success'] = true;

                    $this->paymentDetail->sendEmailStudent($paymentDetail);
                    $data['data'] = $newPaymentDetails;
                }

                if (@$responsePaynamics['response_advise']) {
                    //$data['message'] = "Your transaction has been recorded. Please complete the payment steps.";
                    $data['success'] = true;
                    DB::commit();
                } else {
                    DB::rollback();
                    $data['success'] = false;
                    $data['message'] = "No response from payment gateway. Please contact the system administrator.";
                }
            }
        } catch (Exception $e) {
            report($e);

            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            $data['message'] = strval($e);
            $data['success'] = false;
        }

        return response()->json($data, 200);
    }

    public function modeRelease()
    {
        $data['message'] = 'List of mode release';
        $data['success'] = true;
        $data['data'] = config('finance.MODE_RELEASES');

        return response()->json($data, 200);
    }

    public function deliveryArea()
    {
        $data['data'] = RegionResource::collection($this->region->all());
        $data['success'] = true;
        $data['message'] = 'List of region.';

        return response()->json($data, 200);
    }

    public function cities($code)
    {
        $data['data'] = @CityResource::collection($this->city->where('region_code', $code)->get());
        $data['success'] = true;
        $data['message'] = 'List of cities under region';

        return response()->json($data, 200);
    }
     //cancel payment transactions
     public function cancelTransaction(Request $request)
     {
         // return $request->all();
         $paymentData = $this->paymentDetail->where('request_id', $request->request_id)->first();
 
         if ($paymentData) {
             if (App::environment(['local', 'staging'])) {
                $merchantid = "000000200223B5713D7D";
                $mkey = "AABF87F484B8ECA1DA52B731E2C55A9B";
                $username = 'iacademy8B7j';
                $password = '8B7jcW9FeJ9Y';
                $url = 'https://payin.payserv.net/paygate/transactions/';
             } else {
                 $merchantid = "0000002802239213DA0B";
                 $mkey = "B2A17D5EF9A1EBA62F3C9E30D7198921";
                 $username = 'iAcademy15mh';
                 $password = '15mhj3F3DvoJ';
                 $url = 'https://payin.paynamics.net/paygate/transactions/';
             }
 
             $requestId = $request->request_id;
             $origRequestId = $request->request_id;
             $notURL = route('webhook');
             $responseURL = url()->current();
 
             $rawTrx = $merchantid .
                 $requestId .
                 $origRequestId .
                 $paymentData->ip_address .
                 $notURL .
                 $responseURL .
                 $mkey;
 
             $signatureTrx = hash("sha512", $rawTrx);
 
             $arrPostData = [
                 "request_id" => $requestId,
                 "org_request_id" => $origRequestId,
                 "ip_address" => $paymentData->ip_address,
                 "notification_url" => $notURL,
                 "response_url" => $responseURL,
                 "signature" => $signatureTrx,
             ];
 
             // You can set your post data
             $postData = http_build_query($arrPostData); // Raw PHP array
             $postData = json_encode($arrPostData); // Only USE this when request JSON data.
             $mixResponse = $this->executeCurl(array(
                 CURLOPT_URL => $url,
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_HTTPGET => true,
                 CURLOPT_VERBOSE => true,
                 CURLOPT_AUTOREFERER => true,
                 CURLOPT_CUSTOMREQUEST => 'POST',
                 CURLOPT_POSTFIELDS  => $postData,
                 CURLOPT_HTTPHEADER  => array(
                     "X-HTTP-Method-Override: " . 'POST',
                     'Content-Type: application/json', // Only USE this when requesting JSON data
                 ),
 
                 // If HTTP authentication is required, use the below lines.
                 CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                 CURLOPT_USERPWD  => $username . ':' . $password
             ));
 
             var_export($mixResponse);
             exit;
             // Will dump a beauty json :3
             return $responsePaynamics = json_decode($mixResponse, true);
 
             $data['success'] = true;
             $data['message'] = $responsePaynamics;
         } else {
             $data['success'] = false;
             $data['message'] = 'No transaction found.';
         }
 
         return response()->json($data, 200);
     }

    function executeCurl($arrOptions)
    {

        $mixCH = curl_init();

        foreach ($arrOptions as $strCurlOpt => $mixCurlOptValue) {
            curl_setopt($mixCH, $strCurlOpt, $mixCurlOptValue);
        }

        $mixResponse = curl_exec($mixCH);
        curl_close($mixCH);
        return $mixResponse;
    }
}
