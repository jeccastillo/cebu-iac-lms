<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

/**
 * PaymentDetailsImportService
 *
 * Responsibilities:
 * - Parse .xlsx/.xls/.csv files into normalized associative rows (header -> value)
 * - Validate required fields (student_number)
 * - Resolve student_id from tb_mas_users by student_number
 * - Ensure invoice exists when invoice_number provided (auto-create via InvoiceService if missing)
 * - Upsert payment_details:
 *   - If "id" present: update via PaymentDetailAdminService::update (schema-safe and handles OR uniqueness)
 *   - Else: insert via DB::table with schema-safe mapping from PaymentDetailAdminService::detectColumns()
 *
 * Returns summary: [totalRows, inserted, updated, skipped, errors[]]
 */
class PaymentDetailsImportService
{
    /**
     * Import the uploaded file and return a summary.
     *
     * @param string $path Real path to uploaded file
     * @param string $ext 'xlsx'|'xls'|'csv'
     * @param array $options
     * @return array{totalRows:int,inserted:int,updated:int,skipped:int,errors:array<int,array{line:int,code?:string,message:string}>}
     */
    public function import(string $path, string $ext, array $options = []): array
    {
        $iter = $this->parse($path, $ext);

        $total = 0; $ins = 0; $upd = 0; $skp = 0;
        $errors = [];

        /** @var \App\Services\PaymentDetailAdminService $admin */
        $admin = app(\App\Services\PaymentDetailAdminService::class);
        $cols = $admin->detectColumns();
        $table = $cols['table'] ?? 'payment_details';
        $hasTable = $cols['exists'];

        if (!$hasTable) {
            return [
                'totalRows' => 0,
                'inserted'  => 0,
                'updated'   => 0,
                'skipped'   => 0,
                'errors'    => [
                    ['line' => 0, 'code' => 'TABLE_MISSING', 'message' => 'payment_details table not found'],
                ],
            ];
        }

        foreach ($iter as $item) {
            $total++;
            $line = (int) ($item['line'] ?? 0);
            $row  = $item['data'] ?? [];
            $code = null;

            try {
                // Normalize keys to lowercase for convenience
                $data = [];
                foreach ($row as $k => $v) {
                    $lk = strtolower(trim((string) $k));
                    $data[$lk] = is_string($v) ? trim($v) : $v;
                }

                // Required: student_number
                $studentNumber = (string) ($data['student_number'] ?? '');
                if ($studentNumber === '') {
                    throw new RuntimeException('Missing required field: student_number');
                }

                $studentId = $this->resolveStudentIdByNumber($studentNumber);
                if (!$studentId) {
                    throw new RuntimeException('Student not found for student_number: ' . $studentNumber);
                }

                // Optional: posted_at, map to date column via admin service
                $postedAt = (string) ($data['posted_at'] ?? '');

                // Ensure Invoice exists if invoice_number provided
                $invoiceNumber = $data['invoice_number'] ?? null;
                if ($invoiceNumber !== null && $invoiceNumber !== '') {
                    $syidOpt = $this->toIntOrNull($data['syid'] ?? null);
                    $subtotalOrder = $this->toFloatOrNull($data['subtotal_order'] ?? null);
                    $desc = (string) ($data['description'] ?? '');
                    $this->ensureInvoiceExists((string) $invoiceNumber, (int) $studentId, $syidOpt, $desc, $subtotalOrder, [
                        'posted_at' => $postedAt ?: null,
                        'remarks'   => (string) ($data['remarks'] ?? ''),
                    ]);
                }

                // Upsert rule
                $idRaw = $data['id'] ?? null;
                $hasId = ($idRaw !== null && $idRaw !== '' && is_numeric($idRaw));
                $payload = $this->mapRowToUpdatePayload($data);

                if ($hasId) {
                    // Update via admin service (handles OR uniqueness and mapping)
                    $id = (int) $idRaw;
                    $admin->update($id, $payload, null);
                    $upd++;
                    continue;
                }

                // Insert: build insert array mapped to actual columns
                $insert = [];

                // Student id and student number if columns exist
                if (!empty($cols['student_id'])) {
                    $insert[$cols['student_id']] = (int) $studentId;
                }
                if (!empty($cols['student_number'])) {
                    $insert[$cols['student_number']] = $studentNumber;
                }

                // Scalars
                $this->tryMapScalar($insert, $cols, 'description', $data['description'] ?? null);
                $this->tryMapScalar($insert, $cols, 'subtotal_order', $this->toFloatOrNull($data['subtotal_order'] ?? null));
                $this->tryMapScalar($insert, $cols, 'total_amount_due', $this->toFloatOrNull($data['total_amount_due'] ?? null));
                $this->tryMapScalar($insert, $cols, 'status', $data['status'] ?? null);
                $this->tryMapScalar($insert, $cols, 'remarks', $data['remarks'] ?? null);
                $this->tryMapScalar($insert, $cols, 'mode_of_payment_id', $this->toIntOrNull($data['mode_of_payment_id'] ?? null));

                // Method mapping
                $method = $data['method'] ?? null;
                $paymentMethod = $data['payment_method'] ?? null;
                $methodValue = ($method !== null && $method !== '') ? $method : $paymentMethod;
                if ($methodValue !== null && $methodValue !== '' && !empty($cols['method'])) {
                    $insert[$cols['method']] = $methodValue;
                }

                // Date mapping
                if (!empty($cols['date']) && $postedAt !== '') {
                    $insert[$cols['date']] = $postedAt;
                }

                // Numbers mapping: OR number (pre-check duplicates if provided)
                $newOr = $data['or_no'] ?? ($data['or_number'] ?? null);
                if ($newOr !== null && $newOr !== '' && !empty($cols['number_or'])) {
                    $dup = DB::table($table)->where($cols['number_or'], $newOr)->count();
                    if ($dup > 0) {
                        throw new RuntimeException('OR number already exists in payment_details: ' . $newOr);
                    }
                    $insert[$cols['number_or']] = $newOr;
                }

                // Invoice number mapping
                if (!empty($cols['number_invoice']) && $invoiceNumber !== null && $invoiceNumber !== '') {
                    $insert[$cols['number_invoice']] = $invoiceNumber;
                }

                // Optional syid reference if present in schema (several variants; admin service uses 'sy_reference')
                if (!empty($cols['sy_reference'])) {
                    $syid = $this->toIntOrNull($data['syid'] ?? null);
                    if ($syid !== null) {
                        $insert[$cols['sy_reference']] = $syid;
                    }
                }

                // Timestamps if present
                $now = date('Y-m-d H:i:s');
                if (!empty($cols['created_at']) && !array_key_exists($cols['created_at'], $insert)) {
                    $insert[$cols['created_at']] = $now;
                }
                if (!empty($cols['updated_at']) && !array_key_exists($cols['updated_at'], $insert)) {
                    $insert[$cols['updated_at']] = $now;
                }

                // Perform insert
                DB::table($table)->insertGetId($insert);
                $ins++;

            } catch (\Throwable $e) {
                $skp++;
                $errors[] = [
                    'line' => $line,
                    'code' => $code,
                    'message' => $e->getMessage(),
                ];
                continue;
            }
        }

        return [
            'totalRows' => $total,
            'inserted'  => $ins,
            'updated'   => $upd,
            'skipped'   => $skp,
            'errors'    => $errors,
        ];
    }

    /**
     * Parse uploaded file into generator of normalized rows: ['line' => int, 'data' => array].
     *
     * @param string $path
     * @param string $ext one of xlsx|xls|csv
     * @return \Generator<array{line:int,data:array}>
     */
    public function parse(string $path, string $ext): \Generator
    {
        $ext = strtolower($ext);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $reader = IOFactory::createReader(($ext === 'xls') ? 'Xls' : 'Xlsx');
            $reader->setReadDataOnly(true);
            $ss = $reader->load($path);
            $sheet = $ss->getSheet(0);

            $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            $highestRow = (int) $sheet->getHighestRow();

            // header map
            $header = [];
            for ($c = 1; $c <= $highestCol; $c++) {
                $v = (string) $sheet->getCellByColumnAndRow($c, 1)->getValue();
                $v = trim($v);
                if ($v !== '') {
                    $header[$c] = strtolower($v);
                }
            }

            for ($r = 2; $r <= $highestRow; $r++) {
                $row = [];
                for ($c = 1; $c <= $highestCol; $c++) {
                    if (!isset($header[$c])) continue;
                    $key = $header[$c];
                    $val = $sheet->getCellByColumnAndRow($c, $r)->getFormattedValue();
                    $row[$key] = is_string($val) ? trim($val) : $val;
                }
                if ($this->rowIsEmpty($row)) continue;
                yield ['line' => $r, 'data' => $row];
            }
        } elseif ($ext === 'csv') {
            $fh = fopen($path, 'rb');
            if ($fh === false) {
                throw new RuntimeException('Unable to open uploaded CSV.');
            }
            $header = null;
            $line = 0;
            while (($cols = fgetcsv($fh)) !== false) {
                $line++;
                $cols = array_map(function ($v) {
                    $s = (string) ($v ?? '');
                    return trim($s);
                }, $cols);
                if ($header === null) {
                    $header = array_map(fn($h) => strtolower((string) $h), $cols);
                    continue;
                }
                $row = [];
                foreach ($header as $i => $h) {
                    if ($h === '' || !array_key_exists($i, $cols)) continue;
                    $row[$h] = $cols[$i];
                }
                if ($this->rowIsEmpty($row)) continue;
                yield ['line' => $line, 'data' => $row];
            }
            fclose($fh);
        } else {
            throw new RuntimeException('Unsupported file extension: ' . $ext);
        }
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Ensure an invoice exists for the given invoiceNumber; auto-create if missing.
     * Uses description-based classification and subtotal_order for amount.
     *
     * @param string $invoiceNumber
     * @param int $studentId
     * @param int|null $syid
     * @param string $description
     * @param float|null $subtotalOrder
     * @param array $opts ['posted_at' => ?string, 'remarks' => ?string]
     */
    protected function ensureInvoiceExists(string $invoiceNumber, int $studentId, ?int $syid, string $description, ?float $subtotalOrder, array $opts = []): void
    {
        // Quick existence check
        try {
            $exists = DB::table('tb_mas_invoices')
                ->where('invoice_number', $invoiceNumber)
                ->exists();
            if ($exists) {
                return;
            }
        } catch (\Throwable $e) {
            // If invoice table is not available, skip silently
            return;
        }

        // Classify type
        $type = $this->classifyInvoiceType($description);

        // Amount from subtotal_order per spec
        $amount = ($subtotalOrder !== null) ? (float) $subtotalOrder : 0.0;

        /** @var \App\Services\InvoiceService $invSvc */
        $invSvc = app(\App\Services\InvoiceService::class);

        // Use 0 for syid if null as the service requires an int
        $syidInt = $syid !== null ? (int) $syid : 0;

        // Use "Issued" status per spec
        $invSvc->generate($type, (int) $studentId, (int) $syidInt, [
            'invoice_number' => $invoiceNumber,
            'amount'         => $amount,
            'status'         => 'Issued',
            'posted_at'      => $opts['posted_at'] ?? null,
            'remarks'        => $opts['remarks'] ?? null,
        ], null);
    }

    protected function classifyInvoiceType(string $description): string
    {
        $d = strtolower(trim($description));
        if ($d === 'application payment') return 'application payment';
        if ($d === 'reservation payment') return 'reservation payment';
        if (str_contains($d, 'tuition')) return 'tuition';
        return 'billing';
    }

    protected function resolveStudentIdByNumber(string $studentNumber): ?int
    {
        try {
            $id = DB::table('tb_mas_users')
                ->where('strStudentNumber', $studentNumber)
                ->value('intID');
            if ($id === null) return null;
            return (int) $id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function toIntOrNull($v): ?int
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (int) $v;
        return null;
    }

    protected function toFloatOrNull($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float) $v;
        return null;
    }

    /**
     * Build payload for PaymentDetailAdminService::update
     */
    protected function mapRowToUpdatePayload(array $data): array
    {
        $payload = [];
        $map = [
            'description'        => 'description',
            'subtotal_order'     => 'subtotal_order',
            'total_amount_due'   => 'total_amount_due',
            'status'             => 'status',
            'remarks'            => 'remarks',
            'mode_of_payment_id' => 'mode_of_payment_id',
            'invoice_number'     => 'invoice_number',
        ];
        foreach ($map as $src => $dst) {
            if (array_key_exists($src, $data)) {
                $payload[$dst] = $data[$src];
            }
        }

        // method/payment_method (accept either)
        if (array_key_exists('method', $data)) {
            $payload['method'] = $data['method'];
        }
        if (array_key_exists('payment_method', $data)) {
            $payload['payment_method'] = $data['payment_method'];
        }

        // posted_at
        if (array_key_exists('posted_at', $data)) {
            $payload['posted_at'] = $data['posted_at'];
        }

        // OR number (accept either key)
        if (array_key_exists('or_no', $data)) {
            $payload['or_no'] = $data['or_no'];
        }
        if (array_key_exists('or_number', $data)) {
            $payload['or_number'] = $data['or_number'];
        }

        return $payload;
    }

    private function tryMapScalar(array &$insert, array $cols, string $key, $value): void
    {
        if ($value === null || $value === '' || empty($cols[$key])) {
            return;
        }
        $insert[$cols[$key]] = $value;
    }
}
