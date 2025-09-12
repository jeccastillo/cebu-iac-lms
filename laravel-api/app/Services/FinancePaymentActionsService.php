<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FinancePaymentActionsService
{
    protected PaymentDetailAdminService $admin;

    public function __construct(PaymentDetailAdminService $admin)
    {
        $this->admin = $admin;
    }

    /**
     * Finance-admin search focused on OR/Invoice numbers (with optional student_number/syid).
     * Returns [ 'items' => PaymentDetailItem[], 'meta' => [ 'page', 'per_page', 'total' ] ].
     *
     * @param array $filters {
     *   @var string|null or_number
     *   @var string|null invoice_number
     *   @var string|null student_number
     *   @var int|null    syid
     *   @var string|null status
     * }
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function search(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $or = trim((string)($filters['or_number'] ?? ''));
        $inv = trim((string)($filters['invoice_number'] ?? ''));

        if ($or === '' && $inv === '') {
            throw ValidationException::withMessages([
                'query' => ['At least one of or_number or invoice_number is required.']
            ]);
        }

        // Delegate to Admin service search (schema-safe, normalized)
        // Pass through supported filters (admin service accepts these keys)
        $allowed = [
            'q', 'student_number', 'student_id', 'syid', 'status',
            'mode', 'or_number', 'invoice_number', 'date_from', 'date_to'
        ];
        $f = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $filters)) {
                $f[$k] = $filters[$k];
            }
        }

        return $this->admin->search($f, $page, $perPage);
    }

    /**
     * Set status to "Void" for the given payment_details id.
     * Returns the updated normalized item. Idempotent: if already "Void", still logs an update attempt.
     *
     * @param int $id
     * @param Request|null $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function void(int $id, ?Request $request = null): array
    {
        // Ensure row exists (normalized snapshot)
        $current = $this->admin->getById($id);
        if (!$current) {
            throw ValidationException::withMessages(['id' => ["payment_details id {$id} not found"]]);
        }

        $remarks = (string)($request?->input('remarks') ?? '');
        $payload = [
            'status' => 'Void',
        ];

        // Append/merge remarks if supported by schema
        if ($remarks !== '') {
            $payload['remarks'] = $this->mergeRemarks((string)($current['remarks'] ?? ''), $remarks);
        } else {
            // Optional: tag default remark for traceability if none provided
            $payload['remarks'] = $this->mergeRemarks((string)($current['remarks'] ?? ''), 'Voided by finance');
        }

        return $this->admin->update($id, $payload, $request);
    }

    /**
     * Hard delete the given payment_details id (Retract).
     * Uses PaymentDetailAdminService::delete for transactional delete and logging.
     *
     * @param int $id
     * @param Request|null $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function retract(int $id, ?Request $request = null): void
    {
        $this->admin->delete($id, $request);
    }

    /**
     * Helper to merge remarks with an audit tag, keeping simple formatting.
     *
     * @param string $existing
     * @param string $append
     * @return string
     */
    protected function mergeRemarks(string $existing, string $append): string
    {
        $existing = trim($existing);
        $append = trim($append);
        if ($existing === '') {
            return $append;
        }
        if ($append === '') {
            return $existing;
        }
        // Avoid duplicate tags
        if (str_contains($existing, $append)) {
            return $existing;
        }
        return $existing . ' | ' . $append;
    }
}
