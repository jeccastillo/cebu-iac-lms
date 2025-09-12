<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\InvoiceService;
use App\Models\Cashier;

class StudentBillingExtrasService
{
    /**
     * Normalize a tb_mas_student_billing row to API shape (parity with StudentBillingService::normalizeRow).
     */
    protected function normalizeBillingRow(array $r): array
    {
        return [
            'id'          => (int)($r['intID'] ?? 0),
            'student_id'  => (int)($r['intStudentID'] ?? 0),
            'syid'        => (int)($r['syid'] ?? 0),
            'description' => (string)($r['description'] ?? ''),
            'amount'      => round((float)($r['amount'] ?? 0), 2),
            'posted_at'   => isset($r['posted_at']) ? (string)$r['posted_at'] : null,
            'remarks'     => isset($r['remarks']) ? (string)$r['remarks'] : null,
            'created_at'  => isset($r['created_at']) ? (string)$r['created_at'] : null,
            'updated_at'  => isset($r['updated_at']) ? (string)$r['updated_at'] : null,
        ];
    }

    /**
     * Return a list of billing rows for a student/term that DO NOT have an invoice yet.
     * Strategy:
     *  - Load all billing rows for the student+term.
     *  - Load all invoices for the student+term.
     *  - Consider a billing "invoiced" only if an invoice exists whose billing_id equals the billing intID.
     */
    public function listMissingInvoices(int $studentId, int $term): array
    {
        $billings = DB::table('tb_mas_student_billing')
            ->where('intStudentID', $studentId)
            ->where('syid', $term)            
            ->whereRaw('LOWER(COALESCE(description, \'\')) NOT IN (?, ?)', ['reservation payment','application payment'])
            ->orderBy('posted_at', 'desc')
            ->orderBy('intID', 'desc')
            ->get()
            ->map(fn($r) => $this->normalizeBillingRow((array)$r))
            ->toArray();

        if (empty($billings)) {
            return [];
        }

        // Build a quick lookup of billed rows
        $byId = [];
        foreach ($billings as $b) {
            $byId[(int)$b['id']] = $b;
        }

        // Load candidate invoices for this student/term (any type)
        $invoices = DB::table('tb_mas_invoices')
            ->where('intStudentID', $studentId)
            ->where('syid', $term)
            ->where('type','!=','reservation payment')            
            ->where('type','!=','application payment')            
            ->select('intID', 'type','billing_id','remarks', 'payload')
            ->orderBy('intID', 'desc')
            ->get();        
        // Pre-compute matches: billingId => true if there exists an invoice with invoices.billing_id equal to the billing intID
        $matchedIds = [];
        foreach ($invoices as $inv) {
            $bid = isset($inv->billing_id) ? (int)$inv->billing_id : 0;
            if ($bid > 0 && isset($byId[$bid])) {
                $matchedIds[$bid] = true;
            }
        }
        // Filter out matched billings
        $out = [];
        foreach ($byId as $bid => $b) {
            if (!isset($matchedIds[$bid])) {
                $out[] = $b;
            }
        }
        return $out;
    }

    /**
     * Get single billing (normalized) by id.
     */
    public function getBilling(int $id): ?array
    {
        $row = DB::table('tb_mas_student_billing')->where('intID', $id)->first();
        return $row ? $this->normalizeBillingRow((array)$row) : null;
    }

    /**
     * Check if a billing already has an invoice by direct billing_id linkage.
     * A billing is considered invoiced when an invoice exists for the same student/term
     * whose billing_id equals the billing's id.
     */
    public function hasInvoice(array $billing): bool
    {
        $studentId = (int)($billing['student_id'] ?? 0);
        $term = (int)($billing['syid'] ?? 0);
        $billId = (int)($billing['id'] ?? 0);
        if ($studentId <= 0 || $term <= 0 || $billId <= 0) return false;

        return DB::table('tb_mas_invoices')
            ->where('intStudentID', $studentId)
            ->where('syid', $term)
            ->where('billing_id', $billId)
            ->exists();
    }

    /**
     * Create an invoice for the given billing row.
     * Returns normalized invoice row (from InvoiceService::generate).
     */
    public function generateInvoiceForBilling(int $billingId, int $studentId, int $term, array $opts = [], ?int $actorId = null): array
    {
        $billing = $this->getBilling($billingId);
        if (!$billing || (int)$billing['student_id'] !== (int)$studentId || (int)$billing['syid'] !== (int)$term) {
            throw new \InvalidArgumentException('Billing row not found or mismatched student/term.');
        }

        if ($this->hasInvoice($billing)) {
            throw new \RuntimeException('Billing already invoiced.');
        }

        // Build invoice options (single line item equal to the billing row)
        $options = [
            'billing_id' => $billingId,
            'items'     => [
                [
                    'description' => (string)$billing['description'],
                    'amount'      => round((float)$billing['amount'], 2),
                ],
            ],
            'amount'    => round((float)$billing['amount'], 2),
            'status'    => 'Draft',
            'posted_at' => $opts['posted_at'] ?? null,
            'remarks'   => $opts['remarks'] ?? (!empty($billing['remarks']) ? (string)$billing['remarks'] : ('Billing #' . (int)$billingId)),
        ];

        // Resolve acting cashier by actor faculty id and attach invoice_number when available
        $usedInvoiceNumber = null;
        if ($actorId !== null) {
            $cashier = Cashier::query()->where('faculty_id', (int)$actorId)->first();
            if ($cashier && !empty($cashier->invoice_current)) {
                $options['invoice_number'] = (int)$cashier->invoice_current;
                $options['cashier_id']     = (int)$cashier->intID;                
                $usedInvoiceNumber         = (int)$cashier->invoice_current;
            }
        }

        $svc = app(InvoiceService::class);
        // Type: use a stable tag 'billing' for these generated invoices
        $row = $svc->generate('billing', (int)$studentId, (int)$term, $options, $actorId);

        // Increment pointer when we consumed a number from a resolved cashier
        if ($usedInvoiceNumber !== null) {
            try {
                Cashier::query()
                    ->where('invoice_current', $usedInvoiceNumber)
                    ->where('intID', (int)($options['cashier_id'] ?? 0))
                    ->update(['invoice_current' => $usedInvoiceNumber + 1]);
            } catch (\Throwable $e) {
                // Do not block on pointer increment failure
            }
        }

        return $row;
    }
}
