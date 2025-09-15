<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SavedTuition;
use App\Models\Cashier;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate an invoice for a student/term with a specific type.
     *
     * @param string $type 'tuition'|'billing'|'other'
     * @param int $studentId tb_mas_users.intID
     * @param int $syid School year id
     * @param array $options
     *   - items?: array<array{description:string, amount:float}> (optional, overrides computed)
     *   - amount?: float (optional, overrides computed)
     *   - posted_at?: string|null (Y-m-d H:i:s)
     *   - due_at?: string|null (Y-m-d)
     *   - remarks?: string|null
     *   - status?: string (default 'Draft') - Draft|Issued|Paid|Void
     *   - campus_id?: int|null
     *   - cashier_id?: int|null
     *   - registration_id?: int|null // optional registration link for tuition invoices
     * @param int|null $actorId user id performing the action
     * @return array Normalized invoice row
     */
    public function generate(string $type, int $studentId, int $syid, array $options = [], ?int $actorId = null): array
    {
        $type = strtolower(trim($type));
        // if (!in_array($type, ['tuition', 'billing', 'other'], true)) {
        //     throw new \InvalidArgumentException("Invalid invoice type: {$type}");
        // }

        // Resolve items and total based on type (unless overridden by options)
        $items = [];
        $source = $type;
        $meta = [];

        // Allow explicit override via options['items']
        if (isset($options['items']) && is_array($options['items'])) {
            $items = $this->normalizeItemsArray($options['items']);
            $meta['source_note'] = 'items provided via options';
        } else {
            if ($type === 'tuition') {
                [$items, $meta] = $this->buildFromTuition($studentId, $syid);
            } else {
                [$items, $meta] = $this->buildFromBilling($studentId, $syid);
            }
        }

        // Compute total
        $total = 0.0;
        foreach ($items as $it) {
            $total += (float) ($it['amount'] ?? 0);
        }

        // Allow explicit override via options['amount']
        if (array_key_exists('amount', $options) && is_numeric($options['amount'])) {
            $total = (float) $options['amount'];
            $meta['amount_override'] = true;
        }

        $status = isset($options['status']) && is_string($options['status'])
            ? $options['status']
            : 'Draft';

        $payload = [
            'items' => $items,
            'source' => $source,
            'meta' => $meta,
        ];

        // Resolve campus id as a safety net (controller may also pass one)
        $campusId = $this->resolveCampusId($options, $actorId);

        $row = Invoice::create([
            'intStudentID'    => $studentId,
            'syid'            => $syid,
            'registration_id' => $options['registration_id'] ?? null,
            'billing_id'      => $options['billing_id'] ?? null,
            'type'            => $type,
            'status'          => $status,
            'invoice_number'  => array_key_exists('invoice_number', $options) ? $options['invoice_number'] : null, // allow assignment when provided (e.g., cashier flow)
            'amount_total'    => round($total, 2),
            // Extra amount fields
            'withholding_tax_percentage' => array_key_exists('withholding_tax_percentage', $options) ? $options['withholding_tax_percentage'] : null,
            'invoice_amount'             => array_key_exists('invoice_amount', $options) ? $options['invoice_amount'] : null,
            'invoice_amount_ves'         => array_key_exists('invoice_amount_ves', $options) ? $options['invoice_amount_ves'] : null,
            'invoice_amount_vzrs'        => array_key_exists('invoice_amount_vzrs', $options) ? $options['invoice_amount_vzrs'] : null,
            'posted_at'       => $options['posted_at'] ?? null,
            'due_at'          => $options['due_at'] ?? null,
            'remarks'         => $options['remarks'] ?? null,
            'payload'         => $payload,
            'campus_id'       => $campusId,
            'cashier_id'      => $options['cashier_id'] ?? null,
            'created_by'      => $actorId,
            'updated_by'      => $actorId,
        ]);

        return $this->normalizeRow($row->toArray());
    }

    /**
     * List invoices with optional filters.
     *
     * @param array $filters
     *  - student_id?: int
     *  - student_number?: string
     *  - syid?: int
     *  - type?: string
     *  - status?: string
     *  - campus_id?: int
     *  - registration_id?: int
     * @return array<int,array>
     */
    public function list(array $filters = []): array
    {
        $q = DB::table('tb_mas_invoices as i');

        if (!empty($filters['student_id'])) {
            $q->where('i.intStudentID', (int) $filters['student_id']);
        } elseif (!empty($filters['student_number'])) {
            $q->join('tb_mas_users as u', 'u.intID', '=', 'i.intStudentID');
            $q->where('u.strStudentNumber', (string) $filters['student_number']);
        }

        if (!empty($filters['syid'])) {
            $q->where('i.syid', (int) $filters['syid']);
        }
        if (!empty($filters['type'])) {
            $q->where('i.type', (string) $filters['type']);
        }
        if (!empty($filters['status'])) {
            $q->where('i.status', (string) $filters['status']);
        }
        if (!empty($filters['campus_id'])) {
            $q->where('i.campus_id', (int) $filters['campus_id']);
        }
        if (!empty($filters['registration_id'])) {
            $q->where('i.registration_id', (int) $filters['registration_id']);
        }

        $rows = $q->orderBy('i.created_at', 'desc')->get();

        return $rows->map(function ($r) {
            return $this->normalizeRow((array)$r);
        })->toArray();
    }

    /**
     * Get single invoice by id.
     */
    public function get(int $id): ?array
    {
        $row = DB::table('tb_mas_invoices')->where('intID', $id)->first();
        return $row ? $this->normalizeRow((array)$row) : null;
    }

    /**
     * Attempt to build invoice items from student billing items (positive charges).
     *
     * @return array{0: array<int,array{description:string,amount:float}>, 1: array}
     */
    protected function buildFromBilling(int $studentId, int $syid): array
    {
        $svc = app(\App\Services\StudentBillingService::class);
        // StudentBillingService::list accepts (studentNumber, studentId, syid)
        $items = $svc->list(null, $studentId, $syid);

        $rows = [];
        $charges = 0.0;
        $credits = 0.0;

        foreach ($items as $it) {
            $desc = (string) ($it['description'] ?? '');
            $amt = (float) ($it['amount'] ?? 0);

            if ($amt > 0) {
                $rows[] = ['description' => $desc, 'amount' => round($amt, 2)];
                $charges += $amt;
            } else {
                $credits += abs($amt);
            }
        }

        $meta = [
            'billing_items_count' => count($items),
            'charges_total' => round($charges, 2),
            'credits_total' => round($credits, 2),
            'note' => 'Only positive amounts (charges) included as invoice line items',
        ];

        return [$rows, $meta];
    }

    /**
     * Attempt to build invoice items from latest SavedTuition snapshot.
     * This is best-effort; payload formats can vary.
     *
     * @return array{0: array<int,array{description:string,amount:float}>, 1: array}
     */
    protected function buildFromTuition(int $studentId, int $syid): array
    {
        $saved = SavedTuition::query()
            ->where('intStudentID', $studentId)
            ->where('syid', $syid)
            ->orderBy('updated_at', 'desc')
            ->orderBy('intID', 'desc')
            ->first();

        $rows = [];
        $meta = [
            'saved_tuition_found' => (bool) $saved,
        ];

        if (!$saved) {
            return [$rows, $meta];
        }

        $payload = $saved->payload ?? null;
        $meta['tuition_payload_present'] = $payload !== null;

        // Heuristics: look for common keys for totals and items
        $total = null;

        // Try typical total keys
        $possibleTotalKeys = [
            'total', 'grand_total', 'grandTotal', 'amountPayable', 'totalPayable', 'totals.totalPayable',
        ];
        foreach ($possibleTotalKeys as $key) {
            $val = $this->arrayGetDot($payload, $key);
            if (is_numeric($val)) {
                $total = (float) $val;
                break;
            }
        }

        // Try to construct items from potential 'breakdown' like arrays
        $possibleItemsKeys = [
            'items', 'breakdown', 'charges', 'lines', 'details',
            'totals.items', 'summary.items',
        ];
        foreach ($possibleItemsKeys as $k) {
            $arr = $this->arrayGetDot($payload, $k);
            if (is_array($arr)) {
                foreach ($arr as $line) {
                    $desc = '';
                    if (is_array($line)) {
                        $desc = (string) ($line['description'] ?? $line['name'] ?? $line['label'] ?? '');
                        $amt = isset($line['amount']) ? (float)$line['amount'] :
                               (isset($line['value']) ? (float)$line['value'] : 0.0);
                    } else {
                        // Unsupported structure; skip
                        continue;
                    }
                    if ($desc !== '' && is_numeric($amt) && $amt != 0.0) {
                        $rows[] = ['description' => $desc, 'amount' => round((float)$amt, 2)];
                    }
                }
                if (!empty($rows)) break;
            }
        }

        // If still no items but we have a total, create single-line item
        if (empty($rows) && $total !== null) {
            $rows[] = ['description' => 'Tuition', 'amount' => round($total, 2)];
        }

        $meta['tuition_items_count'] = count($rows);
        if ($total !== null) {
            $meta['tuition_total_detected'] = round($total, 2);
        }

        return [$rows, $meta];
    }

    /**
     * Normalize an array of items to the standard shape.
     *
     * @param array $items
     * @return array<int, array{description:string, amount:float}>
     */
    protected function normalizeItemsArray(array $items): array
    {
        $out = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;
            $desc = isset($it['description']) ? (string)$it['description'] : '';
            $amt  = isset($it['amount']) ? (float)$it['amount'] : 0.0;
            if ($desc !== '' && is_numeric($amt)) {
                $out[] = ['description' => $desc, 'amount' => round($amt, 2)];
            }
        }
        return $out;
    }

    /**
     * Normalize DB row to API shape.
     *
     * @param array $r
     * @return array
     */
    protected function normalizeRow(array $r): array
    {
        return [
            'id'            => (int) ($r['intID'] ?? 0),
            'student_id'    => (int) ($r['intStudentID'] ?? 0),
            'syid'          => (int) ($r['syid'] ?? 0),
            'registration_id'=> isset($r['registration_id']) ? (int) $r['registration_id'] : null,
            'billing_id'     => isset($r['billing_id']) ? (int) $r['billing_id'] : null,
            'type'          => (string) ($r['type'] ?? ''),
            'status'        => (string) ($r['status'] ?? ''),
            'invoice_number'=> isset($r['invoice_number']) ? $r['invoice_number'] : null,
            'amount_total'  => round((float) ($r['amount_total'] ?? 0), 2),
            // Expose extra amount fields
            'withholding_tax_percentage' => array_key_exists('withholding_tax_percentage', $r) ? (int) $r['withholding_tax_percentage'] : null,
            'invoice_amount'             => array_key_exists('invoice_amount', $r) ? (float) $r['invoice_amount'] : null,
            'invoice_amount_ves'         => array_key_exists('invoice_amount_ves', $r) ? (float) $r['invoice_amount_ves'] : null,
            'invoice_amount_vzrs'        => array_key_exists('invoice_amount_vzrs', $r) ? (float) $r['invoice_amount_vzrs'] : null,
            'posted_at'     => isset($r['posted_at']) ? (string) $r['posted_at'] : null,
            'due_at'        => isset($r['due_at']) ? (string) $r['due_at'] : null,
            'remarks'       => isset($r['remarks']) ? (string) $r['remarks'] : null,
            'payload'       => isset($r['payload']) ? (is_array($r['payload']) ? $r['payload'] : (json_decode((string)$r['payload'], true) ?: null)) : null,
            'campus_id'     => isset($r['campus_id']) ? (int) $r['campus_id'] : null,
            'cashier_id'    => isset($r['cashier_id']) ? (int) $r['cashier_id'] : null,
            'created_by'    => isset($r['created_by']) ? (int) $r['created_by'] : null,
            'updated_by'    => isset($r['updated_by']) ? (int) $r['updated_by'] : null,
            'created_at'    => isset($r['created_at']) ? (string) $r['created_at'] : null,
            'updated_at'    => isset($r['updated_at']) ? (string) $r['updated_at'] : null,
        ];
    }

    /**
     * Dot-notation array getter helper.
     */
    protected function arrayGetDot($array, string $key, $default = null)
    {
        if (!is_array($array)) return $default;
        if ($key === '') return $array;

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        $segments = explode('.', $key);
        $current = $array;
        foreach ($segments as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];
            } else {
                return $default;
            }
        }
        return $current;
    }

    /**
     * Upsert a tuition invoice by registration_id. If an invoice exists for this registration_id and type 'tuition',
     * update its amount_total to the provided amount. Otherwise create a new 'tuition' invoice.
     *
     * @param int $registrationId
     * @param int $studentId
     * @param int $syid
     * @param float $amount
     * @param array $options
     *  - status?: string
     *  - remarks?: string|null
     *  - posted_at?: string|null
     *  - due_at?: string|null
     *  - campus_id?: int|null
     *  - cashier_id?: int|null
     * @param int|null $actorId
     * @return array Normalized invoice row
     */
    public function upsertTuitionByRegistration(int $registrationId, int $studentId, int $syid, float $amount, array $options = [], ?int $actorId = null): array
    {
        $existing = DB::table('tb_mas_invoices')
            ->where('registration_id', $registrationId)
            ->where('type', 'tuition')
            ->orderBy('intID', 'desc')
            ->first();

        // Resolve campus as fallback for both update/create paths
        $campusIdResolved = $this->resolveCampusId($options, $actorId);

        $now = now()->toDateTimeString();

        // Normalize options
        $status = isset($options['status']) && is_string($options['status']) ? $options['status'] : 'Draft';
        $hasCampus   = array_key_exists('campus_id', $options);
        $hasPayload  = array_key_exists('payload', $options);
        $hasInvNo    = array_key_exists('invoice_number', $options);

        if ($existing) {
            $update = [
                'amount_total' => round($amount, 2),
                'remarks'      => array_key_exists('remarks', $options) ? $options['remarks'] : $existing->remarks,
                'posted_at'    => array_key_exists('posted_at', $options) ? $options['posted_at'] : $existing->posted_at,
                'due_at'       => array_key_exists('due_at', $options) ? $options['due_at'] : $existing->due_at,
                'updated_by'   => $actorId,
                'updated_at'   => $now,
            ];

            // Allow updating campus_id if provided
            if ($hasCampus) {
                $update['campus_id'] = $options['campus_id'];
            } elseif ($campusIdResolved !== null && (empty($existing->campus_id))) {
                // If not explicitly provided and existing row lacks campus, set from resolved campus
                $update['campus_id'] = $campusIdResolved;
            }
            // Allow replacing payload if provided (array expected)
            if ($hasPayload) {
                $payloadOpt = $options['payload'];
                // DB::table() does not apply Eloquent casts; ensure JSON is encoded on update
                $update['payload'] = is_array($payloadOpt) ? json_encode($payloadOpt) : $existing->payload;
            }
            // Allow setting cashier_id if provided
            if (array_key_exists('cashier_id', $options)) {
                $update['cashier_id'] = $options['cashier_id'];
            }
            // Allow setting invoice_number if currently empty/null and provided
            if ($hasInvNo && (empty($existing->invoice_number))) {
                $update['invoice_number'] = $options['invoice_number'];
            }

            DB::table('tb_mas_invoices')
                ->where('intID', $existing->intID)
                ->update($update);

            $row = DB::table('tb_mas_invoices')->where('intID', $existing->intID)->first();
            return $this->normalizeRow((array)$row);
        }

        // Create new invoice using known amount
        $row = Invoice::create([
            'intStudentID'    => $studentId,
            'syid'            => $syid,
            'registration_id' => $registrationId,
            'type'            => 'tuition',
            'status'          => $status,
            'invoice_number'  => $hasInvNo ? $options['invoice_number'] : null,
            'amount_total'    => round($amount, 2),
            'posted_at'       => $options['posted_at'] ?? null,
            'due_at'          => $options['due_at'] ?? null,
            'remarks'         => $options['remarks'] ?? null,
            'payload'         => $hasPayload ? (is_array($options['payload']) ? $options['payload'] : ['source' => 'tuition-save', 'meta' => []]) : ['source' => 'tuition-save', 'meta' => []],
            'campus_id'       => $campusIdResolved,
            'cashier_id'      => $options['cashier_id'] ?? null,
            'created_by'      => $actorId,
            'updated_by'      => $actorId,
        ]);

        return $this->normalizeRow($row->toArray());
    }

    /**
     * Compute the total paid amount for a given invoice number from payment_details.
     * Only counts rows with status 'Paid'. Handles both numeric and string invoice numbers.
     */
    public function getInvoicePaidTotal($invoiceNumber): float
    {
        if ($invoiceNumber === null || $invoiceNumber === '') {
            return 0.0;
        }

        // Guard schema access
        try {
            $sum = DB::table('payment_details')
                ->where('invoice_number', $invoiceNumber)
                ->where('status', 'Paid')
                ->sum('subtotal_order');

            // subtotal_order in payment_details is positive for credits (payments) in many places.
            // Ensure we return a positive "paid total" number.
            $paid = (float) $sum;
            if ($paid < 0) {
                $paid = abs($paid);
            }
            return round($paid, 2);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Compute remaining amount for an invoice.
     * remaining = max(0, amount_total - paid_total)
     */
    public function getInvoiceRemaining($invoiceNumber): float
    {
        $inv = null;
        try {
            $inv = DB::table('tb_mas_invoices')
                ->where('invoice_number', $invoiceNumber)
                ->orderBy('intID', 'desc')
                ->first();
        } catch (\Throwable $e) {
            $inv = null;
        }

        $total = 0.0;
        if ($inv && isset($inv->amount_total)) {
            $total = (float) $inv->amount_total;
        } else {
            // Fallback: try normalized getter when invoice_number resolves to a single row id
            $row = $this->get((int) $invoiceNumber);
            if (is_array($row)) {
                $total = (float) ($row['amount_total'] ?? 0.0);
            }
        }

        $paid = $this->getInvoicePaidTotal($invoiceNumber);
        $remaining = max(0.0, (float) $total - (float) $paid);
        return round($remaining, 2);
    }

    /**
     * Resolve campus id from options or actor's cashier context as fallback.
     *
     * @param array $options
     * @param int|null $actorId
     * @return int|null
     */
    protected function resolveCampusId(array $options, ?int $actorId): ?int
    {
        if (isset($options['campus_id']) && is_numeric($options['campus_id'])) {
            return (int) $options['campus_id'];
        }
        if ($actorId !== null) {
            $cashier = Cashier::query()->where('faculty_id', (int) $actorId)->first();
            if ($cashier && isset($cashier->campus_id)) {
                return (int) $cashier->campus_id;
            }
        }
        return null;
    }
}
