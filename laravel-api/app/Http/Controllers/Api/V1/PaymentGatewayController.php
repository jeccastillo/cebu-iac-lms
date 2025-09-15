<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payments\CheckoutRequest;
use App\Models\PaymentDetail;
use App\Models\PaymentMode;
use App\Models\Admissions\AdmissionStudentInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceService;
use Throwable;

class PaymentGatewayController extends Controller
{
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $data = [];
        try {
            $mailingFee = (float) ($request->input('mailing_fee', 0) ?? 0.0);
            $subtotal = (float) $request->input('total_price_without_charge', 0);
            $total = (float) $request->input('total_price_with_charge', 0);
            $chargeDefault = (float) $request->input('charge', 0);

            $modePayment = PaymentMode::find((int) $request->input('mode_of_payment_id'));
            if (!$modePayment || !$modePayment->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or inactive mode of payment.',
                ], 422);
            }

            // Recompute charge if type is percentage
            $computedCharge = $chargeDefault;
            if ($modePayment->type === 'percentage') {
                $percentInDecimal = ((float) $modePayment->charge) / 100;
                $computedCharge = round($percentInDecimal * $subtotal, 2);
                if ($computedCharge < 28) {
                    $computedCharge = 28.00;
                }
            }

            // Fail fast on charge mismatch to ensure parity with legacy behavior
            if (number_format($chargeDefault, 2, '.', '') !== number_format($computedCharge, 2, '.', '')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed, wrong computations of charges.',
                    'charge' => [
                        'default' => number_format($chargeDefault, 2, '.', ''),
                        'computed' => number_format($computedCharge, 2, '.', ''),
                    ],
                    'subtotal' => $subtotal + $mailingFee,
                    'mailing_cost' => $mailingFee,
                    'total' => $total,
                ]);
            }

            // Optional: validate invoice remaining and capture references
            $syid = (int) ($request->input('syid') ?? 0);
            $invoiceNumberRef = $request->input('invoice_number') ?? null;
            // For invoice remaining validation, compare ONLY the base payment amount (exclude convenience charges and mailing fee)
            $amountToApply = (float) $subtotal;
            if (!empty($invoiceNumberRef)) {
                /** @var InvoiceService $invSvc */
                $invSvc = app(\App\Services\InvoiceService::class);
                $remaining = $invSvc->getInvoiceRemaining($invoiceNumberRef);
                if ($amountToApply > ($remaining + 0.01)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Amount exceeds invoice remaining.',
                        'invoice_number' => $invoiceNumberRef,
                        'invoice_remaining' => $remaining,
                        'attempt_amount' => $amountToApply
                    ], 422);
                }
            }

            // Request ID (gateway limit: max 32 chars, no dashes). Use compact UUID without long prefixes.
            $requestId = substr(str_replace('-', '', (string) Str::uuid()), 0, 32);

            // Fetch student (optional info; some fields may not exist in current schema)
            $student = AdmissionStudentInformation::find((int) $request->input('student_information_id'));

            // Create PaymentDetail row (fill only known safe columns)
            $p = new PaymentDetail();
            $p->request_id = $requestId;
            $p->slug = (string) Str::uuid();
            $p->description = (string) $request->input('description', '');
            $p->student_information_id = (int) $request->input('student_information_id');
            $p->student_number = (string) $request->input('student_number', '');
            $p->first_name = (string) $request->input('first_name', '');
            $p->middle_name = (string) $request->input('middle_name', '');
            $p->last_name = (string) $request->input('last_name', '');
            $p->email_address = (string) $request->input('email', '');
            $p->mode_of_payment_id = (int) $request->input('mode_of_payment_id');
            $p->remarks = (string) $request->input('remarks', '');
            // Persist computed convenience fee and charges (use validated computed charge to avoid rounding mismatches)
            $p->convenience_fee = (float) $computedCharge;
            $p->subtotal_order = $subtotal + $mailingFee;
            $p->total_amount_due = $total + $mailingFee;
            $p->charges = (float) $computedCharge;
            $p->contact_number = (string) $request->input('contact_number', '');

            // Resolve student campus name (non-null) from assigned campus:
            // Priority:
            //  1) tb_mas_users.campus_id (by student_information_id or student_number) -> tb_mas_campuses.campus_name
            //  2) tb_mas_sy.campus_id (by syid) -> tb_mas_campuses.campus_name
            //  3) Fallback 'Unknown' to satisfy NOT NULL constraint
            $studentCampusName = null;
            try {
                $sid = (int) ($request->input('student_information_id') ?? 0);
                $snum = (string) ($request->input('student_number') ?? '');

                if (Schema::hasTable('tb_mas_users')) {
                    // Prefer lookup by intID, fallback to student_number
                    $uq = DB::table('tb_mas_users as u');
                    if ($sid > 0) {
                        $uq->where('u.intID', $sid);
                    } elseif ($snum !== '') {
                        $uq->where('u.strStudentNumber', $snum);
                    } else {
                        $uq = null;
                    }

                    if ($uq) {
                        if (Schema::hasTable('tb_mas_campuses') && Schema::hasColumn('tb_mas_users', 'campus_id')) {
                            $row = $uq->leftJoin('tb_mas_campuses as c', 'c.id', '=', 'u.campus_id')
                                ->select('u.campus_id', 'c.campus_name')
                                ->first();
                            if ($row && !empty($row->campus_name)) {
                                $studentCampusName = (string) $row->campus_name;
                            }
                        }
                    }
                }

                if (!$studentCampusName && $syid > 0 && Schema::hasTable('tb_mas_sy') && Schema::hasColumn('tb_mas_sy', 'campus_id')) {
                    $cid = DB::table('tb_mas_sy')->where('intID', $syid)->value('campus_id');
                    if ($cid && Schema::hasTable('tb_mas_campuses')) {
                        $cn = DB::table('tb_mas_campuses')->where('id', (int)$cid)->value('campus_name');
                        if (!empty($cn)) {
                            $studentCampusName = (string) $cn;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // swallow and fallback
            }
            $p->student_campus = $studentCampusName ?: 'Unknown';
            $p->ip_address = (string) $request->ip();

            // Persist optional references when columns exist
            try {
                if (!empty($invoiceNumberRef) && \Illuminate\Support\Facades\Schema::hasColumn('payment_details', 'invoice_number')) {
                    $p->invoice_number = $invoiceNumberRef;
                }
                if ($syid > 0) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('payment_details', 'sy_reference')) {
                        $p->sy_reference = $syid;
                    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('payment_details', 'syid')) {
                        $p->syid = $syid;
                    }
                }
            } catch (\Throwable $e) {
                // ignore optional ref persistence failures
            }

            // Safe-guard: persist; schema mismatches will be surfaced here
            $p->save();

            // Build item lines for gateways
            $orderItems = $request->input('order_items', []);
            $paynamicsOrders = $this->buildPaynamicsOrderLines($orderItems, $subtotal, $mailingFee, (float) $computedCharge, (string) $modePayment->pmethod, (string) $modePayment->pchannel);

            // Resolve environment config
            $env = config('payments.environment', App::environment());
            $isStaging = in_array($env, ['local', 'staging'], true);

            // Frontend URLs
            $responseURL = config('payments.frontend.success_url', '/#/payments/success');
            $cancelURL = config('payments.frontend.cancel_url', '/#/payments/cancel');

            // Determine gateway branch
            $pmethod = (string) $modePayment->pmethod;

            if (in_array($pmethod, ['onlinebanktransfer', 'wallet'], true)) {
                // Paynamics (OBT/Wallet)
                $conf = config('payments.paynamics');
                $url = $isStaging ? ($conf['url']['staging'] ?? '') : ($conf['url']['prod'] ?? '');
                $merchantid = (string) $conf['merchant_id'];
                $mkey = (string) $conf['mkey'];
                $username = (string) $conf['username'];
                $password = (string) $conf['password'];
                $pchannel = (string) $modePayment->pchannel;
                $paymentAction = $pmethod;
                $collectionMethod = 'single_pay';
                $currency = 'PHP';
                $payNotStatus = '1';
                // UBP Paynamics notifications use channel '1' for ubp_online in legacy
                $payNotChannel = $pchannel === 'ubp_online' ? '1' : '';

                $amount = number_format($total + $mailingFee, 2, '.', '');
                $signatureTrx = $this->signPaynamicsTransaction(
                    $merchantid,
                    $requestId,
                    route('payments.webhook.paynamics'),
                    $responseURL,
                    $cancelURL,
                    $pmethod,
                    $paymentAction,
                    $collectionMethod,
                    $amount,
                    $currency,
                    $payNotStatus,
                    $payNotChannel,
                    $mkey
                );

                $fname = (string) $request->input('first_name', '');
                $lname = (string) $request->input('last_name', '');
                $mname = (string) $request->input('middle_name', '');
                $email = (string) $request->input('email', '');
                $phone = (string) $request->input('contact_number', '');
                $mobile = (string) $request->input('contact_number', '');
                $dob = $request->input('dob', null);
                $signatureCustomer = $this->signCustomer($fname, $lname, $mname, $email, $phone, $mobile, $dob, $mkey);

                $payload = [
                    'transaction' => [
                        'request_id' => $requestId,
                        'notification_url' => route('payments.webhook.paynamics'),
                        'response_url' => $responseURL,
                        'cancel_url' => $cancelURL,
                        'pmethod' => $pmethod,
                        'pchannel' => $pchannel,
                        'payment_action' => $paymentAction,
                        'schedule' => '',
                        'collection_method' => $collectionMethod,
                        'deferred_period' => '',
                        'deferred_time' => '',
                        'dp_balance_info' => '',
                        'amount' => $amount,
                        'currency' => $currency,
                        'pay_reference' => '',
                        'payment_notification_status' => $payNotStatus,
                        'payment_notification_channel' => $payNotChannel,
                        'signature' => $signatureTrx,
                    ],
                    'customer_info' => [
                        'fname' => $fname,
                        'lname' => $lname,
                        'mname' => $mname,
                        'email' => $email,
                        'phone' => $phone,
                        'mobile' => $mobile,
                        'dob' => $dob,
                        'signature' => $signatureCustomer,
                    ],
                    'order_details' => [
                        'orders' => $paynamicsOrders,
                        // Paynamics expects subtotalprice and totalorderamount to equal sum of order lines:
                        // subtotal (base) + convenience fee + mailing fee
                        'subtotalprice' => (float) ($subtotal + $computedCharge + $mailingFee),
                        'shippingprice' => '0.00',
                        'discountamount' => '0.00',
                        'totalorderamount' => (float) ($subtotal + $computedCharge + $mailingFee),
                    ],
                ];

                $resp = $this->httpJson($url, $payload, $username, $password);
                // Persist response essentials
                $p->response_message = $resp['response_message'] ?? null;
                $p->response_advise = $resp['response_advise'] ?? null;
                $p->payment_action_info = $resp['payment_action_info'] ?? null;
                $p->response_id = $resp['response_id'] ?? null;
                if ($modePayment->is_nonbank && isset($resp['direct_otc_info'][0])) {
                    $p->pay_reference = $resp['direct_otc_info'][0]['pay_reference'] ?? null;
                    $p->pay_instructions = $resp['direct_otc_info'][0]['pay_instructions'] ?? null;
                }
                $p->remarks = 'Paynamics';
                $p->save();

                return response()->json([
                    'success' => true,
                    'gateway' => 'paynamics',
                    'request_id' => $requestId,
                    'payment_link' => $p->payment_action_info ?? ($p->pay_reference ?? null),
                    'notification_url' => route('payments.webhook.paynamics'),
                    'response_url' => $responseURL,
                    'cancel_url' => $cancelURL,
                    'data' => $resp,
                ]);
            }

            if ($pmethod === 'nonbank_otc') {
                // Paynamics (OTC)
                $conf = config('payments.paynamics');
                $url = $isStaging ? ($conf['url']['staging'] ?? '') : ($conf['url']['prod'] ?? '');
                $merchantid = (string) $conf['merchant_id'];
                $mkey = (string) $conf['mkey'];
                $username = (string) $conf['username'];
                $password = (string) $conf['password'];
                $pchannel = (string) $modePayment->pchannel;
                $paymentAction = $pmethod;
                $collectionMethod = 'single_pay';
                $currency = 'PHP';
                $payNotStatus = '1';
                $payNotChannel = $pchannel === 'ubp_online' ? '1' : '';

                $amount = number_format($total + $mailingFee, 2, '.', '');
                $signatureTrx = $this->signPaynamicsTransaction(
                    $merchantid,
                    $requestId,
                    route('payments.webhook.paynamics'),
                    $responseURL,
                    $cancelURL,
                    $pmethod,
                    $paymentAction,
                    $collectionMethod,
                    $amount,
                    $currency,
                    $payNotStatus,
                    $payNotChannel,
                    $mkey
                );

                $todayDateTime = date('m/d/Y h:i:s', strtotime(date('Y/m/d') . ' + 2 days'));

                $fname = (string) $request->input('first_name', '');
                $lname = (string) $request->input('last_name', '');
                $mname = (string) $request->input('middle_name', '');
                $email = (string) $request->input('email', '');
                $phone = (string) $request->input('contact_number', '');
                $mobile = (string) $request->input('contact_number', '');
                $dob = $request->input('dob', null);
                $signatureCustomer = $this->signCustomer($fname, $lname, $mname, $email, $phone, $mobile, $dob, $mkey);

                $payload = [
                    'transaction' => [
                        'request_id' => $requestId,
                        'notification_url' => route('payments.webhook.paynamics'),
                        'response_url' => $responseURL,
                        'cancel_url' => $cancelURL,
                        'pmethod' => $pmethod,
                        'pchannel' => $pchannel,
                        'payment_action' => $paymentAction,
                        'schedule' => '',
                        'collection_method' => $collectionMethod,
                        'deferred_period' => '',
                        'deferred_time' => '',
                        'dp_balance_info' => '',
                        'amount' => $amount,
                        'currency' => $currency,
                        'descriptor_note' => '',
                        'payment_notification_status' => $payNotStatus,
                        'payment_notification_channel' => $payNotChannel,
                        'expiry_limit' => $todayDateTime,
                        'signature' => $signatureTrx,
                    ],
                    'customer_info' => [
                        'fname' => $fname,
                        'lname' => $lname,
                        'mname' => $mname,
                        'email' => $email,
                        'phone' => $phone,
                        'mobile' => $mobile,
                        'dob' => $dob,
                        'signature' => $signatureCustomer,
                    ],
                    'billing_info' => [
                        'billing_address1' => '',
                        'billing_address2' => '',
                        'billing_city' => '',
                        'billing_state' => '',
                        'billing_country' => 'PH',
                        'billing_zip' => '',
                    ],
                    'order_details' => [
                        'orders' => $paynamicsOrders,
                        // Subtotal and total must equal sum of order lines (subtotal + convenience fee + mailing fee)
                        'subtotalprice' => (float) ($subtotal + $computedCharge + $mailingFee),
                        'shippingprice' => '0.00',
                        'discountamount' => '0.00',
                        'totalorderamount' => (float) ($subtotal + $computedCharge + $mailingFee),
                    ]
                ];

                $resp = $this->httpJson($url, $payload, $username, $password);
                $p->response_message = $resp['response_message'] ?? null;
                $p->response_advise = $resp['response_advise'] ?? null;
                $p->payment_action_info = $resp['payment_action_info'] ?? null;
                $p->response_id = $resp['response_id'] ?? null;
                if (isset($resp['direct_otc_info'][0])) {
                    $p->pay_reference = $resp['direct_otc_info'][0]['pay_reference'] ?? null;
                    $p->pay_instructions = $resp['direct_otc_info'][0]['pay_instructions'] ?? null;
                }
                $p->remarks = 'Paynamics';
                $p->save();

                return response()->json([
                    'success' => true,
                    'gateway' => 'paynamics',
                    'request_id' => $requestId,
                    'payment_link' => $p->pay_reference ?? $p->payment_action_info ?? null,
                    'notification_url' => route('payments.webhook.paynamics'),
                    'response_url' => $responseURL,
                    'cancel_url' => $cancelURL,
                    'data' => $resp,
                    'message' => 'Please check your email for payment instructions.',
                ]);
            }

            if ($pmethod === 'bdo_pay') {
                // BDO / CyberSource Secure Acceptance: return signed fields for client auto-submit
                $conf = config('payments.bdo');
                $fields = $this->buildBdoFields([
                    'access_key' => (string) $conf['access_key'],
                    'profile_id' => (string) $conf['profile_id'],
                    'signed_field_names' => (string) $conf['signed_fields'],
                    'transaction_type' => (string) $conf['transaction_type'],
                    'currency' => (string) $conf['currency'],
                    'locale' => (string) $conf['locale'],
                    'secret_key' => (string) $conf['secret_key'],
                    'reference_number' => $requestId,
                    'amount' => number_format($total + $mailingFee, 2, '.', ''),
                    'bill_to' => array_merge([
                        'bill_to_email' => (string) $request->input('bill_to_email', $request->input('email', '')),
                        'bill_to_surname' => (string) $request->input('bill_to_surname', $request->input('last_name', '')),
                        'bill_to_forename' => (string) $request->input('bill_to_forename', $request->input('first_name', '')),
                    ], $conf['bill_to'] ?? []),
                ]);

                $p->remarks = 'BDO Pay';
                $p->save();

                return response()->json([
                    'success' => true,
                    'gateway' => 'bdo_pay',
                    'request_id' => $requestId,
                    'post_data' => $fields,
                    'action_url' => (string) $conf['url'],
                ]);
            }

            if ($pmethod === 'maxx_payment') {
                // MaxxPayment (BDO installment provider)
                $conf = config('payments.maxx');
                $urlBase = $isStaging ? ($conf['url']['staging'] ?? '') : ($conf['url']['prod'] ?? '');
                $mc_tc = (string) $conf['mc_code'];
                $options = (string) $conf['options_json'];
                $successUrl = config('payments.frontend.success_url', '/#/payments/success');
                $failureUrl = config('payments.frontend.failure_url', '/#/payments/failure');
                $cancelUrl = config('payments.frontend.cancel_url', '/#/payments/cancel');

                // Build direct URL (provider accepts query params)
                $fullUrl = $urlBase .
                    '&SC_MC=' . rawurlencode($mc_tc) .
                    '&SC_AMOUNT=' . rawurlencode(number_format($total + $mailingFee, 2, '.', '')) .
                    '&SC_REF=' . rawurlencode($requestId) .
                    '&SC_OPTIONS=' . rawurlencode($options) .
                    '&SC_SUCCESSURL=' . rawurlencode($successUrl) .
                    '&SC_FAILURL=' . rawurlencode($failureUrl) .
                    '&SC_CANCELURL=' . rawurlencode($cancelUrl) .
                    '&SC_CUR_DATA=PHP|1&SC_FREDIRECT=1';

                $p->remarks = 'BDO installment';
                $p->save();

                return response()->json([
                    'success' => true,
                    'gateway' => 'maxx_payment',
                    'request_id' => $requestId,
                    'payment_link' => $fullUrl,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unsupported payment method.',
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request): JsonResponse
    {
        // Cancel transaction via Paynamics
        $requestId = (string) $request->input('request_id', '');
        if ($requestId === '') {
            return response()->json([
                'success' => false,
                'message' => 'request_id is required'
            ], 422);
        }

        $paymentData = PaymentDetail::where('request_id', $requestId)->first();
        if (!$paymentData) {
            return response()->json([
                'success' => false,
                'message' => 'No transaction found.',
            ], 404);
        }

        try {
            $conf = config('payments.paynamics');
            $env = config('payments.environment', App::environment());
            $isStaging = in_array($env, ['local', 'staging'], true);
            $url = $isStaging ? ($conf['url']['staging'] ?? '') : ($conf['url']['prod'] ?? '');
            $merchantid = (string) $conf['merchant_id'];
            $mkey = (string) $conf['mkey'];
            $username = (string) $conf['username'];
            $password = (string) $conf['password'];

            $origRequestId = $requestId;
            $notURL = route('payments.webhook.paynamics');
            $responseURL = url()->current();

            $rawTrx = $merchantid .
                $requestId .
                $origRequestId .
                ($paymentData->ip_address ?? '') .
                $notURL .
                $responseURL .
                $mkey;

            $signatureTrx = hash('sha512', $rawTrx);

            $payload = [
                'request_id' => $requestId,
                'org_request_id' => $origRequestId,
                'ip_address' => $paymentData->ip_address ?? request()->ip(),
                'notification_url' => $notURL,
                'response_url' => $responseURL,
                'signature' => $signatureTrx,
            ];

            $resp = $this->httpJson($url, $payload, $username, $password);

            return response()->json([
                'success' => true,
                'message' => 'Cancel request submitted.',
                'response' => $resp,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildPaynamicsOrderLines(array $orderItems, float $subtotal, float $mailingFee, float $convenienceFee, string $pmethod, string $pchannel): array
    {
        $lines = [];
        foreach ($orderItems as $orderItem) {
            $qty = (int) ($orderItem['qty'] ?? 0);
            $unit = (float) ($orderItem['price_default'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $lines[] = [
                'itemname' => (string) ($orderItem['title'] ?? 'Item'),
                'quantity' => $qty,
                'unitprice' => $unit,
                'totalprice' => $unit * $qty,
            ];
        }

        // Add Convenience Fee and Mailing Fee as separate lines so that
        // sum(orders) = subtotal + convenienceFee + mailingFee and matches totalorderamount.
        $feeDelta = max(0.0, (float) $convenienceFee);
        if ($feeDelta > 0 && in_array($pmethod, ['onlinebanktransfer', 'wallet', 'nonbank_otc'], true)) {
            $lines[] = [
                'itemname' => 'Convenience Fee',
                'quantity' => 1,
                'unitprice' => $feeDelta,
                'totalprice' => $feeDelta,
            ];
        }
        if ($mailingFee > 0) {
            $lines[] = [
                'itemname' => 'Mailing Fee',
                'quantity' => 1,
                'unitprice' => (float) $mailingFee,
                'totalprice' => (float) $mailingFee,
            ];
        }

        return $lines;
    }

    private function signPaynamicsTransaction(
        string $merchantid,
        string $requestId,
        string $notificationUrl,
        string $responseUrl,
        string $cancelUrl,
        string $pmethod,
        string $paymentAction,
        string $collectionMethod,
        string $amount,
        string $currency,
        string $paymentNotificationStatus,
        string $paymentNotificationChannel,
        string $mkey
    ): string {
        $raw = $merchantid .
            $requestId .
            $notificationUrl .
            $responseUrl .
            $cancelUrl .
            $pmethod .
            $paymentAction .
            $collectionMethod .
            $amount .
            $currency .
            $paymentNotificationStatus .
            $paymentNotificationChannel .
            $mkey;

        return hash('sha512', $raw);
    }

    private function signCustomer(string $fname, string $lname, string $mname, string $email, string $phone, string $mobile, $dob, string $mkey): string
    {
        $raw = $fname . $lname . $mname . $email . $phone . $mobile . (string) $dob . $mkey;
        return hash('sha512', $raw);
    }

    private function httpJson(string $url, array $payload, ?string $username = null, ?string $password = null): array
    {
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'X-HTTP-Method-Override: POST',
        ];
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_VERBOSE => false,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($username && $password) {
            $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $opts[CURLOPT_USERPWD] = $username . ':' . $password;
        }

        foreach ($opts as $k => $v) {
            curl_setopt($ch, $k, $v);
        }
        $respRaw = curl_exec($ch);
        if ($respRaw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('HTTP request failed: ' . $err);
        }
        curl_close($ch);

        $resp = json_decode($respRaw, true);
        if (!is_array($resp)) {
            // Non-JSON or empty
            return ['raw' => $respRaw];
        }
        return $resp;
    }

    private function buildBdoFields(array $context): array
    {
        $accessKey = $context['access_key'];
        $profileId = $context['profile_id'];
        $signedFieldNames = $context['signed_field_names'];
        $trxType = $context['transaction_type'];
        $currency = $context['currency'];
        $locale = $context['locale'];
        $secretKey = $context['secret_key'];
        $referenceNumber = $context['reference_number'];
        $amount = $context['amount'];
        $billTo = $context['bill_to'];

        $params = [
            'access_key' => $accessKey,
            'profile_id' => $profileId,
            'transaction_uuid' => uniqid('', true),
            'signed_field_names' => $signedFieldNames,
            'unsigned_field_names' => '',
            'signed_date_time' => gmdate('Y-m-d\TH:i:s\Z'),
            'locale' => $locale,
            'transaction_type' => $trxType,
            'reference_number' => $referenceNumber,
            'amount' => $amount,
            'currency' => $currency,
            'bill_to_address_line1' => $billTo['address_line1'] ?? '',
            'bill_to_address_city' => $billTo['address_city'] ?? '',
            'bill_to_address_country' => $billTo['address_country'] ?? 'PH',
            'bill_to_email' => $billTo['bill_to_email'] ?? '',
            'bill_to_surname' => $billTo['bill_to_surname'] ?? '',
            'bill_to_forename' => $billTo['bill_to_forename'] ?? '',
        ];

        // Build data to sign
        $signedFields = explode(',', $params['signed_field_names']);
        $dataToSign = [];
        foreach ($signedFields as $field) {
            $field = trim($field);
            $dataToSign[] = $field . '=' . $params[$field];
        }
        $dataString = implode(',', $dataToSign);

        $signature = base64_encode(hash_hmac('sha256', $dataString, $secretKey, true));
        $params['signature'] = $signature;

        return $params;
    }
}
