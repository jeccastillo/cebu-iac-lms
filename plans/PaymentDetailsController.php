<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\{PaymentsResource, RegionResource};
use App\Models\Finance\FinancePaymentOrderItem;
use App\Models\Finance\FinancePaymentDetail;
use App\Models\Finance\FinancePaymentItem;
use App\Models\Finance\FinancePaymentMode;
use App\Models\{Region, City};
use App\Http\Resources\CityResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception,

App;

class PaymentDetailController extends Controller
{
    //
    public function __construct(
        FinancePaymentDetail $paymentDetail,
        FinancePaymentItem $item,
        FinancePaymentMode $mode,
        FinancePaymentOrderItem $paymentItem,
        Region $region,
        City $city
    ) {

        $this->paymentDetail = $paymentDetail;
        $this->item = $item;
        $this->mode = $mode;
        $this->paymentItem = $paymentItem;
        $this->region = $region;
        $this->city = $city;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        // return 'test';
        $searchField = $request->search_field;
        $searchData = $request->search_data;
        $orderBy = $request->order_by;

        $paginateCount = 10;
        if ($request->count_content) {
            $paginateCount = $request->count_content;
        }

        //check department
        if (auth()->user()->department_id == config('settings.ADMISSIONS')) {
            $departmentId = config('settings.ADMISSIONS');
        } elseif (auth()->user()->department_id == config('settings.REGISTRAR')) {
            $departmentId = config('settings.REGISTRAR');
        } elseif (auth()->user()->department_id == config('settings.ELPD')) {
            $departmentId = config('settings.ELPD');
        } else {
            //show all to finance
            $departmentId = '';
        }

        $payments = $this->paymentDetail->filterByField($searchField, $searchData)
                                ->orderByField($searchField, $orderBy)
                                ->department($departmentId)
                                ->status(request('status'))
                                ->paginate($paginateCount);

        if ($payments) {
            return PaymentsResource::collection($payments);
            $data['message'] = 'Shows Payment history available.';
        } else {
            $data['message'] = 'No Payment history available';
        }

        return response()->json($data, 200);
    }

    public function studentType()
    {
        //
        $types = [
            [
                'title' => 'I am a Senior High School Student Applicant',
                'type' => 'shs',
            ],
            [
                'title' => 'I am a College Student Applicant',
                'type' => 'college',
            ],
            [
                'title' => 'I am an iACADEMY Pro Student Applicant',
                'type' => 'iacademy_pro',
            ],
            [
                'title' => 'I am an Existing Senior High School Student',
                'type' => 'existing_shs',
            ],
            [
                'title' => 'I am an Existing College Student',
                'type' => 'existing_college',
            ]
        ];

        $data['message'] = 'List of student type';
        $data['success'] = true;
        $data['data'] = $types;

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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        //
        $checkExist = $this->paymentDetail->where('slug', $slug)->first();

        if ($checkExist) {
            $data['data'] = new PaymentsResource($checkExist);
            $data['message'] = 'Shows mode of payment details';
            $data['success'] = true;
        } else {
            $data['data'] = null;
            $data['message'] = 'Not found payment details.';
            $data['success'] = false;
        }

        return response()->json($data, 200);
    }

    public function payment(Request $request)
    {
        //
        DB::beginTransaction();
        try {
            //check if mailing..
            $mailingFee = 0;
            if (request('mode_of_release') == config('finance.MODE_RELEASES')[4]['key']) {
                if (request('delivery_region_id') == config('finance.REGIONS_NCR')) {
                    $mailingFee = 200;
                } elseif (request('delivery_region_id') == config('finance.REGIONS_INTNAL')) {
                    if (request('country') != 'Others') {
                        $mailingFee = 2500;
                    }
                } else {
                    $mailingFee = 250;
                }
            }

            $subtotal = $request->total_price_without_charge;
            $total = $request->total_price_with_charge;

            $modePayment = $this->mode->find($request->mode_of_payment_id);
            $chargeDefault = $request->charge;

            if ($modePayment->type == 'percentage') {
                //My number is subtotal
                //I want to get $modePayment->charge
                //Convert our percentage value into a decimal.
                $percentInDecimal = $modePayment->charge / 100;
                //Get the result.
                $charge = $percentInDecimal * $subtotal;

                if ($charge < 25) {
                    $charge = 25;
                }
            } else {
                $charge = $chargeDefault;
            }

            if ($chargeDefault != $charge || $mailingFee != request('mailing_fee')) {
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

            $newPaymentDetails = new $this->paymentDetail();
            $newPaymentDetails->request_id = $requestId;
            $newPaymentDetails->slug = \Str::uuid();
            $newPaymentDetails->description = $request->description;
            $newPaymentDetails->student_number = $request->student_number;
            $newPaymentDetails->first_name = $request->first_name;
            $newPaymentDetails->middle_name = $request->middle_name;
            $newPaymentDetails->last_name = $request->last_name;
            $newPaymentDetails->email_address = $request->email;
            $newPaymentDetails->remarks = $request->remarks;
            $newPaymentDetails->mode_of_payment_id = $request->mode_of_payment_id;
            $newPaymentDetails->remarks = $request->remarks;
            $newPaymentDetails->convenience_fee = @$modePayment->charge;
            $newPaymentDetails->subtotal_order = $subtotal + $mailingFee;
            $newPaymentDetails->total_amount_due = $total + $mailingFee;
            $newPaymentDetails->charges = $total - $subtotal;
            $newPaymentDetails->mailing_fee = @$mailingFee;
            $newPaymentDetails->contact_number = $request->contact_number;
            $newPaymentDetails->name_of_school = @$request->name_of_school;
            $newPaymentDetails->course = @$request->course;
            $newPaymentDetails->mode_of_release = @$request->mode_of_release;
            $newPaymentDetails->delivery_region_id = @$request->delivery_region_id;
            $newPaymentDetails->delivery_city_id = @$request->delivery_city_id;
            $newPaymentDetails->country = @$request->country;
            $newPaymentDetails->other_country = @$request->other_country;

            $newPaymentDetails->ip_address = @$request->ip();
            $newPaymentDetails->save();

            if ($request->order_items) {
                $paynamicsOrders = [];
                foreach ($request->order_items as $orderItem) {
                    $newPaymentItem = new $this->paymentItem();
                    $newPaymentItem->price = $orderItem['price_default'];
                    $newPaymentItem->qty = $orderItem['qty'];
                    $newPaymentItem->item_id = $orderItem['id'];
                    $newPaymentItem->finance_payment_details_id = $newPaymentDetails->id;
                    $newPaymentItem->finance_payment_details_id = $newPaymentDetails->id;
                    $newPaymentItem->grade_level = @$orderItem['grade_level'];
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
                $url = 'https://payin.payserv.net/paygate/transactions/';
            } else {
                $merchantid = "0000002802239213DA0B";
                $mkey = "B2A17D5EF9A1EBA62F3C9E30D7198921";
                $username = 'iAcademy15mh';
                $password = '15mhj3F3DvoJ';
                $url = 'https://payin.paynamics.net/paygate/transactions/';
            }

            $requestId = $newPaymentDetails->request_id;
            $notURL = route('webhook');
            $responseURL = url('/');
            $cancelURL = $modePayment->payment_action == 'onlinebanktransfer' ? '' : route('cancel-payment');
            $pmethod = $modePayment->pmethod;
            $paymentAction = $modePayment->payment_action;
            $pchannel = $modePayment->pchannel;
            $collectionMethod = 'single_pay';
            $amount = $total + $mailingFee;  //charge for payment mode.
            $currency = "PHP";
            $payNotStatus = "1";
            $payNotChannel = $modePayment->payment_action == 'onlinebanktransfer' ? "1" : "";


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
            $requestType = 'POST'; // This can be PUT or POST
            $arrPostData = '';

            $charge = $modePayment->charge;

            if ($modePayment->type == 'percentage') {
                $charge = round(($modePayment->charge * 100) / $subtotal, 2);
            }

            if ($pmethod == 'onlinebanktransfer' || $pmethod == 'wallet') {
                $paynamicsOrders[] = [
                    "itemname" => "Fee",
                    "quantity" => 1,
                    "unitprice" => $total - $subtotal,
                    "totalprice" => $total - $subtotal
                ];

                $arrPostData = [
                    "transaction" => [
                        "request_id" => $requestId,
                        "notification_url" => $notURL,
                        "response_url" => $responseURL,
                        "cancel_url" => $cancelURL,
                        "pmethod" => $pmethod,
                        "pchannel" => $pchannel,
                        "payment_action" => $paymentAction,
                        "collection_method" => $collectionMethod,
                        "payment_notification_status" => $payNotStatus,
                        "payment_notification_channel" => $payNotChannel,
                        "amount" => $total,
                        "currency" => $currency,
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
                        "subtotalprice" => $subtotal,
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

            // return $arrPostData;
            // You can set your post data
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

            if ($modePayment->payment_action == 'direct_otc') {
                $paymentDetail->pay_reference = $responsePaynamics['direct_otc_info'][0]['pay_reference'];
                $paymentDetail->pay_instructions = $responsePaynamics['direct_otc_info'][0]['pay_instructions'];
            }

            $paymentDetail->update();

            

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
                $data['payment_link'] = @$responsePaynamics['direct_otc_info'][0]['pay_reference'];
                $data['message'] = 'Please check your email for the payment instructions.';
                $data['success'] = true;

                $this->paymentDetail->sendEmailStudent($paymentDetail);
                // $data['data'] = $newPaymentDetails;
            }

            if (@$responsePaynamics['response_advise']) {
                $data['message'] = "Your transaction has been recorded. Please complete the payment steps.";
                $data['success'] = true;
                DB::commit();
            } else {
                DB::rollback();
                $data['success'] = false;
                $data['message'] = "No response from payment gateway. Please contact the system administrator.";
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
