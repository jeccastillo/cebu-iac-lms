<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class PaymentDetailAdminService
{
    /**
     * Detect existing columns on payment_details and return a normalized mapping.
     */
    public function detectColumns(): array
    {
        $table = 'payment_details';
        $exists = Schema::hasTable($table);

        $col = function (string $name) use ($table): ?string {
            return Schema::hasColumn($table, $name) ? $name : null;
        };

        // Prefer specific variants when available
        $numberOr = $col('or_no') ?: $col('or_number');
        $numberInvoice = $col('invoice_number');

        // Method may be 'method' or 'payment_method'
        $method = $col('method') ?: $col('payment_method');

        // Prefer 'paid_at', then 'date', else 'created_at'
        $date = $col('paid_at') ?: ($col('date') ?: $col('created_at'));

        return [
            'table'              => $table,
            'exists'             => $exists,
            'id'                 => $col('id') ?: 'id',
            'student_id'         => $col('student_information_id'),
            'student_number'     => $col('student_number'),
            'student_campus'     => $col('student_campus'),
            'sy_reference'       => $col('sy_reference'),
            'description'        => $col('description'),
            'subtotal_order'     => $col('subtotal_order'),
            'total_amount_due'   => $col('total_amount_due'),
            'status'             => $col('status'),
            'remarks'            => $col('remarks'),
            'mode_of_payment_id' => $col('mode_of_payment_id'),
            'number_or'          => $numberOr,
            'number_invoice'     => $numberInvoice,
            'method'             => $method,
            'date'               => $date,
            'created_at'         => $col('created_at'),
            'updated_at'         => $col('updated_at'),
        ];
    }

    /**
     * Build a base select list with normalized aliases.
     */
    protected function selectList(array $cols): array
    {
        $select = [];
        $select[] = 'id';
        if ($cols['student_id']) $select[] = $cols['student_id'] . ' as student_information_id';
        if ($cols['student_number']) $select[] = $cols['student_number'] . ' as student_number';
        if ($cols['sy_reference']) $select[] = $cols['sy_reference'] . ' as sy_reference';
        if ($cols['description']) $select[] = $cols['description'] . ' as description';
        if ($cols['subtotal_order']) $select[] = $cols['subtotal_order'] . ' as subtotal_order';
        if ($cols['total_amount_due']) $select[] = $cols['total_amount_due'] . ' as total_amount_due';
        if ($cols['status']) $select[] = $cols['status'] . ' as status';
        if ($cols['remarks']) $select[] = $cols['remarks'] . ' as remarks';
        if ($cols['mode_of_payment_id']) $select[] = $cols['mode_of_payment_id'] . ' as mode_of_payment_id';

        // Optional variants normalized
        $select[] = $cols['number_or'] ? ($cols['number_or'] . ' as or_no') : DB::raw('NULL as or_no');
        $select[] = $cols['number_invoice'] ? ($cols['number_invoice'] . ' as invoice_number') : DB::raw('NULL as invoice_number');
        $select[] = $cols['method'] ? ($cols['method'] . ' as method') : DB::raw('NULL as method');
        $select[] = $cols['date'] ? ($cols['date'] . ' as posted_at') : DB::raw('NULL as posted_at');

        return $select;
    }

    /**
     * Normalize a DB row object into array shape.
     */
    protected function normalizeRow(object $r): array
    {
        return [
            'id'                 => (int) $r->id,
            'student_information_id' => isset($r->student_information_id) ? (int) $r->student_information_id : null,
            'student_number'     => isset($r->student_number) ? (string) $r->student_number : null,
            'sy_reference'       => isset($r->sy_reference) ? (int) $r->sy_reference : null,
            'description'        => isset($r->description) ? (string) $r->description : null,
            'subtotal_order'     => isset($r->subtotal_order) ? (float) $r->subtotal_order : null,
            'total_amount_due'   => isset($r->total_amount_due) ? (float) $r->total_amount_due : null,
            'status'             => isset($r->status) ? (string) $r->status : null,
            'remarks'            => isset($r->remarks) ? (string) $r->remarks : null,
            'mode_of_payment_id' => isset($r->mode_of_payment_id) ? (int) $r->mode_of_payment_id : null,
            'method'             => isset($r->method) ? (string) $r->method : null,
            'or_no'              => $r->or_no ?? null,
            'invoice_number'     => $r->invoice_number ?? null,
            'posted_at'          => isset($r->posted_at) ? (string) $r->posted_at : null,
            'source'             => 'payment_details',
        ];
    }

    /**
     * Search payment_details for admin with filters and pagination.
     * Returns [ 'items' => [], 'meta' => [ 'page'=>, 'per_page'=>, 'total'=> ] ]
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $cols = $this->detectColumns();
        if (!$cols['exists']) {
            return ['items' => [], 'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => 0]];
        }

        $q = DB::table($cols['table']);
        $select = $this->selectList($cols);
        $q->select($select);

        // Filters
        $qStr = trim((string) ($filters['q'] ?? ''));
        if ($qStr !== '') {
            $q->where(function ($qb) use ($cols, $qStr) {
                if ($cols['student_number']) $qb->orWhere($cols['student_number'], 'like', '%' . $qStr . '%');
                if ($cols['description']) $qb->orWhere($cols['description'], 'like', '%' . $qStr . '%');
                if ($cols['number_or']) $qb->orWhere($cols['number_or'], 'like', '%' . $qStr . '%');
                if ($cols['number_invoice']) $qb->orWhere($cols['number_invoice'], 'like', '%' . $qStr . '%');
                // If numeric term, also test exact matches
                if (is_numeric($qStr)) {
                    if ($cols['number_or']) $qb->orWhere($cols['number_or'], '=', $qStr);
                    if ($cols['number_invoice']) $qb->orWhere($cols['number_invoice'], '=', $qStr);
                }
            });
        }

        if (!empty($filters['student_number']) && $cols['student_number']) {
            $q->where($cols['student_number'], (string) $filters['student_number']);
        }

        if (!empty($filters['student_id']) && $cols['student_id']) {
            $q->where($cols['student_id'], (int) $filters['student_id']);
        }

        if (!empty($filters['syid']) && $cols['sy_reference']) {
            // In this DB, payment_details.sy_reference stores registration or AY id depending on environment.
            // We retain simple equality filter as per existing code patterns.
            $q->where($cols['sy_reference'], (int) $filters['syid']);
        }

        if (!empty($filters['status']) && $cols['status']) {
            $q->where($cols['status'], (string) $filters['status']);
        }

        if (!empty($filters['mode']) && is_string($filters['mode'])) {
            // no-op for now; used by OR/Invoice number filters below
        }

        if (!empty($filters['or_number']) && $cols['number_or']) {
            $needle = (string) $filters['or_number'];
            $q->where($cols['number_or'], 'like', '%' . $needle . '%');
        }

        if (!empty($filters['invoice_number']) && $cols['number_invoice']) {
            $needle = (string) $filters['invoice_number'];
            $q->where($cols['number_invoice'], 'like', '%' . $needle . '%');
        }

        $dateFrom = array_key_exists('date_from', $filters) ? trim((string) $filters['date_from']) : null;
        $dateTo   = array_key_exists('date_to', $filters) ? trim((string) $filters['date_to']) : null;
        if ($cols['date']) {
            if ($dateFrom !== null && $dateFrom !== '') {
                $q->where($cols['date'], '>=', $dateFrom);
            }
            if ($dateTo !== null && $dateTo !== '') {
                $q->where($cols['date'], '<=', $dateTo);
            }
        }

        // Ordering: newest first by date if available, then by number(s), then id
        if ($cols['date']) $q->orderBy($cols['date'], 'desc');
        if ($cols['number_or']) $q->orderBy($cols['number_or'], 'desc');
        if ($cols['number_invoice']) $q->orderBy($cols['number_invoice'], 'desc');
        $q->orderBy('id', 'desc');

        // Pagination
        $total = (clone $q)->count();
        $rows = $q->forPage(max(1, $page), max(1, $perPage))->get();
        $items = [];
        foreach ($rows as $r) {
            $items[] = $this->normalizeRow($r);
        }

        return [
            'items' => $items,
            'meta'  => [
                'page'     => (int) $page,
                'per_page' => (int) $perPage,
                'total'    => (int) $total,
            ],
        ];
    }

    /**
     * Get a single normalized row by ID.
     */
    public function getById(int $id): ?array
    {
        $cols = $this->detectColumns();
        if (!$cols['exists']) {
            return null;
        }

        $q = DB::table($cols['table']);
        $q->where('id', $id);
        $q->select($this->selectList($cols));
        $r = $q->first();
        if (!$r) {
            return null;
        }
        return $this->normalizeRow($r);
    }

    /**
     * Update a row with normalized payload and column mapping.
     * Returns the updated normalized item.
     *
     * Allowed fields (conditionally mapped):
     *  - description, subtotal_order, total_amount_due, status, remarks,
     *  - method/payment_method,
     *  - mode_of_payment_id,
     *  - posted_at (mapped to paid_at/date/created_at),
     *  - or_no/or_number and invoice_number (when columns exist).
     */
    public function update(int $id, array $payload, ?Request $request = null): array
    {
        $cols = $this->detectColumns();
        if (!$cols['exists']) {
            throw ValidationException::withMessages(['payment_details' => ['payment_details table not found']]);
        }

        // Ensure row exists
        $current = DB::table($cols['table'])->where('id', $id)->first();
        if (!$current) {
            throw ValidationException::withMessages(['id' => ["payment_details id {$id} not found"]]);
        }

        // Capture normalized current snapshot for logging
        $old = $this->getById($id);

        // Build update data with safe mapping
        $update = [];

        // Scalar fields
        $mapScalars = [
            'description'      => 'description',
            'subtotal_order'   => 'subtotal_order',
            'total_amount_due' => 'total_amount_due',
            'status'           => 'status',
            'remarks'          => 'remarks',
            'mode_of_payment_id' => 'mode_of_payment_id',
        ];
        foreach ($mapScalars as $reqKey => $colName) {
            if (array_key_exists($reqKey, $payload) && $cols[$colName]) {
                $update[$cols[$colName]] = $payload[$reqKey];
            }
        }

        // Method mapping
        if (array_key_exists('method', $payload) && $cols['method']) {
            $update[$cols['method']] = $payload['method'];
        }
        if (array_key_exists('payment_method', $payload) && $cols['method']) {
            // Accept either key on the wire
            $update[$cols['method']] = $payload['payment_method'];
        }

        // Date mapping (posted_at)
        if (array_key_exists('posted_at', $payload) && $cols['date']) {
            $update[$cols['date']] = $payload['posted_at'];
        }

        // Number columns
        if (array_key_exists('or_no', $payload) || array_key_exists('or_number', $payload)) {
            $newOr = $payload['or_no'] ?? $payload['or_number'] ?? null;
            if ($newOr !== null) {
                if ($cols['number_or']) {
                    // Uniqueness check for OR number
                    $dup = DB::table($cols['table'])
                        ->where($cols['number_or'], $newOr)
                        ->where('id', '!=', $id)
                        ->count();
                    if ($dup > 0) {
                        throw ValidationException::withMessages([
                            'or_no' => ['OR number already exists in payment_details']
                        ]);
                    }
                    $update[$cols['number_or']] = $newOr;
                }
            }
        }

        if (array_key_exists('invoice_number', $payload)) {
            $newInv = $payload['invoice_number'];
            if ($newInv !== null && $cols['number_invoice']) {
                $dup = DB::table($cols['table'])
                    ->where($cols['number_invoice'], $newInv)
                    ->where('id', '!=', $id)
                    ->count();
                if ($dup > 0) {
                    throw ValidationException::withMessages([
                        'invoice_number' => ['Invoice number already exists in payment_details']
                    ]);
                }
                $update[$cols['number_invoice']] = $newInv;
            }
        }

        if (empty($update)) {
            // No-op update - return current
            return $this->getById($id) ?? [];
        }

        DB::transaction(function () use ($cols, $id, $update) {
            DB::table($cols['table'])->where('id', $id)->update($update);
        });

        $updated = $this->getById($id);
        if (!$updated) {
            throw ValidationException::withMessages(['id' => ['Failed to fetch updated row']]);
        }

        // System log: update payment detail
        SystemLogService::log('update', 'PaymentDetail', (int) $id, $old ?? null, $updated, $request);

        return $updated;
    }

    /**
     * Delete a payment_details row by ID. Throws when not found.
     */
    public function delete(int $id, ?Request $request = null): void
    {
        $cols = $this->detectColumns();
        if (!$cols['exists']) {
            throw ValidationException::withMessages(['payment_details' => ['payment_details table not found']]);
        }

        // Ensure row exists
        $current = DB::table($cols['table'])->where('id', $id)->first();
        if (!$current) {
            throw ValidationException::withMessages(['id' => ["payment_details id {$id} not found"]]);
        }

        // Capture normalized old snapshot for logging
        $old = $this->getById($id);

        DB::transaction(function () use ($cols, $id) {
            DB::table($cols['table'])->where('id', $id)->delete();
        });

        // System log: delete payment detail
        SystemLogService::log('delete', 'PaymentDetail', (int) $id, $old, null, $request);
    }
}
