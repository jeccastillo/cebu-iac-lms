<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentDetail;
use Illuminate\Http\Request;

class PaymentsWebhookController extends Controller
{
    // Paynamics webhook (JSON payload)
    public function paynamics(Request $request)
    {
        $response = $request->all();

        $requestId = $response['request_id'] ?? null;
        if (!$requestId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing request_id'
            ], 422);
        }

        $paymentDetails = PaymentDetail::where('request_id', $requestId)->first();
        if (!$paymentDetails) {
            return response()->json([
                'success' => false,
                'message' => 'Request ID not found.',
            ], 404);
        }

        $responseMessage = $response['response_message'] ?? '';
        $paymentDetails->remarks = 'Paynamics';
        $paymentDetails->response_message = $responseMessage;
        $paymentDetails->response_advise = $response['response_advise'] ?? null;

        if ($responseMessage === 'Transaction Successful') {
            $paymentDetails->status = 'Paid';
            $paymentDetails->date_paid = $response['date_paid'] ?? date('F d, Y', strtotime(date('Y-m-d')));
            $paymentDetails->is_sent_email = 1;
            // Fire success email
            $paymentDetails->sendEmailAfterPayment($paymentDetails);
        } elseif ($responseMessage === 'Transaction Expired') {
            $paymentDetails->status = 'Expired';
            $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
            // Fire expired email
            $paymentDetails->sendEmailExpired($paymentDetails);
        } else {
            $paymentDetails->status = $responseMessage ?: 'Unknown';
        }

        $paymentDetails->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated the status',
        ]);
    }

    // BDO/CyberSource webhook (form POST)
    public function bdo(Request $request)
    {
        $response = $request->all();
        $requestId = $response['req_reference_number'] ?? null;

        if (!$requestId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing req_reference_number'
            ], 422);
        }

        $paymentDetails = PaymentDetail::where('request_id', $requestId)->first();
        if (!$paymentDetails) {
            return response()->json([
                'success' => false,
                'message' => 'Request ID not found.',
            ], 404);
        }

        $decision = $response['decision'] ?? '';
        $message = $response['message'] ?? 'Successful Transaction';
        $paymentDetails->remarks = 'BDO Pay';
        $paymentDetails->response_message = $message;
        $paymentDetails->response_advise = $message;

        if ($decision === 'ACCEPT') {
            $paymentDetails->status = 'Paid';
            $paymentDetails->date_paid = $response['date_paid'] ?? date('F d, Y', strtotime(date('Y-m-d')));
            $paymentDetails->is_sent_email = 1;
            $paymentDetails->sendEmailAfterPayment($paymentDetails);
            $paymentDetails->save();
            // Redirect to frontend success page
            return redirect(config('payments.frontend.success_url', '/#/payments/success'));
        } elseif ($decision === 'DECLINE') {
            $paymentDetails->status = 'DECLINED';
            $paymentDetails->save();
            return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
        } else {
            $paymentDetails->status = $decision ?: 'Unknown';
            $paymentDetails->save();
            return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
        }
    }

    // MaxxPayment webhook
    public function maxx(Request $request)
    {
        $response = $request->all();
        if (!isset($response['sc_values'])) {
            // Treat as cancellation
            $requestId = $response['SC_REF'] ?? null;
            if ($requestId) {
                $paymentDetails = PaymentDetail::where('request_id', $requestId)->first();
                if ($paymentDetails) {
                    $paymentDetails->status = 'Cancelled';
                    $paymentDetails->is_sent_email = 1;
                    $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
                    $paymentDetails->remarks = 'BDO installment';
                    $paymentDetails->save();
                }
            }
            return redirect(config('payments.frontend.cancel_url', '/#/payments/cancel'));
        }

        $sc = json_decode($response['sc_values'], true);
        if (!is_array($sc)) {
            return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
        }

        $requestId = $sc['SC_REF'] ?? null;
        if (!$requestId) {
            return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
        }

        $paymentDetails = PaymentDetail::where('request_id', $requestId)->first();
        if (!$paymentDetails) {
            return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
        }

        $status = $sc['SC_STATUS'] ?? '';
        $paymentDetails->remarks = 'BDO installment';

        if ($status === 'approved') {
            $paymentDetails->status = 'Paid';
            $paymentDetails->date_paid = $sc['date_paid'] ?? date('F d, Y', strtotime(date('Y-m-d')));
            $paymentDetails->is_sent_email = 1;
            $paymentDetails->save();

            $paymentDetails->sendEmailAfterPayment($paymentDetails);
            return redirect(config('payments.frontend.success_url', '/#/payments/success'));
        }

        if ($status === 'declined' || $status === 'declined2') {
            $paymentDetails->status = 'Failed';
            $paymentDetails->is_sent_email = 1;
            $paymentDetails->save();
            return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
        }

        if ($status === 'down') {
            $paymentDetails->status = 'Expired';
            $paymentDetails->is_sent_email = 1;
            $paymentDetails->date_expired = date('F d, Y', strtotime(date('Y-m-d')));
            $paymentDetails->save();
            return redirect(config('payments.frontend.cancel_url', '/#/payments/cancel'));
        }

        // Default fallback
        $paymentDetails->status = $status ?: 'Unknown';
        $paymentDetails->save();
        return redirect(config('payments.frontend.failure_url', '/#/payments/failure'));
    }
}
