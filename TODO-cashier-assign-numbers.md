public function assignNumber($cashierId, $paymentId, CashierPaymentAssignNumberRequest $request)
{
    // Validate the payment exists and has no number assigned
    $payment = DB::table('payment_details')->where('id', $paymentId)->first();
    if (!$payment || $payment->or_no || $payment->invoice_number) {
        throw ValidationException::withMessages([
            'payment' => ['Payment not found or already has a number assigned.']
        ]);
    }

    // Validate the cashier has available numbers in their range
    $cashier = Cashier::findOrFail($cashierId);
    $type = $request->input('type');
    $nextNumber = $this->svc->getNextAvailableNumber($cashier, $type);

    if (!$nextNumber) {
        throw ValidationException::withMessages([
            'number' => ['No available numbers in the cashier\'s range.']
        ]);
    }

    // Assign the next available number based on type (OR/Invoice)
    if ($type === 'or') {
        DB::table('payment_details')->where('id', $paymentId)->update(['or_no' => $nextNumber]);
        $cashier->or_current++;
    } else {
        DB::table('payment_details')->where('id', $paymentId)->update(['invoice_number' => $nextNumber]);
        $cashier->invoice_current++;
    }

    // Increment the cashier's counter
    $cashier->save();

    // Log the action in system logs
    SystemLogService::log('assign_number', 'PaymentDetail', $paymentId, null, [
        'type' => $type,
        'number' => $nextNumber,
        'cashier_id' => $cashierId,
    ]);

    return response()->json(['success' => true, 'message' => 'Number assigned successfully.']);
}
