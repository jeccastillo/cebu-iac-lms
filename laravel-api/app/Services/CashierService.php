<?php

namespace App\Services;

use App\Models\Cashier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CashierService
{
    /**
     * Validate overlap across other cashiers' ranges.
     *
     * @param 'or'|'invoice' $type
     * @param int $start
     * @param int $end
     * @param int|null $excludeId
     * @param int|null $campusId
     * @return array ['ok' => bool, 'conflict' => array|null]
     */
    public function validateRangeOverlap(string $type, int $start, int $end, ?int $excludeId = null, ?int $campusId = null): array
    {
        [$colStart, $colEnd] = $type === 'invoice'
            ? ['invoice_start', 'invoice_end']
            : ['or_start', 'or_end'];

        $q = Cashier::query()
            ->select('intID', 'user_id', $colStart, $colEnd, 'campus_id')
            ->when(!is_null($excludeId), fn($qq) => $qq->where('intID', '<>', $excludeId))
            ->when(!is_null($campusId), fn($qq) => $qq->where(function ($w) use ($campusId) {
                $w->where('campus_id', $campusId)->orWhereNull('campus_id');
            }));

        $rows = $q->get();

        foreach ($rows as $row) {
            $otherStart = (int)($row->{$colStart} ?? 0);
            $otherEnd   = (int)($row->{$colEnd} ?? 0);
            if ($otherStart === 0 && $otherEnd === 0) {
                continue;
            }
            // Overlap exists if not (end < otherStart or start > otherEnd)
            if (!($end < $otherStart || $start > $otherEnd)) {
                return [
                    'ok' => false,
                    'conflict' => [
                        'cashier_id' => (int)$row->intID,
                        'user_id'    => (int)$row->user_id,
                        'start'      => $otherStart,
                        'end'        => $otherEnd,
                    ],
                ];
            }
        }

        return ['ok' => true, 'conflict' => null];
    }

    /**
     * Validate that no existing transactions already used numbers within the proposed range.
     * Returns the first conflicting number if found.
     *
     * @param 'or'|'invoice' $type
     * @param int $start
     * @param int $end
     * @return array ['ok' => bool, 'usedNumber' => int|null, 'source' => string|null]
     */
    public function validateRangeUsage(string $type, int $start, int $end): array
    {
        // Primary finance records table: payment_details (based on FinanceService)
        $table = 'payment_details';

        $col = null;
        if ($type === 'invoice') {
            // Likely 'invoice_number'
            if (Schema::hasColumn($table, 'invoice_number')) {
                $col = 'invoice_number';
            }
        } else {
            // OR may be 'or_no' or 'or_number'
            if (Schema::hasColumn($table, 'or_no')) {
                $col = 'or_no';
            } elseif (Schema::hasColumn($table, 'or_number')) {
                $col = 'or_number';
            }
        }

        if ($col) {
            $row = DB::table($table)
                ->select($col)
                ->whereNotNull($col)
                ->whereBetween($col, [$start, $end])
                ->orderBy($col, 'asc')
                ->first();
            if ($row && isset($row->{$col})) {
                return ['ok' => false, 'usedNumber' => (int)$row->{$col}, 'source' => "{$table}.{$col}"];
            }
        }

        // Add any additional tables/columns checks below if needed in future:
        // e.g., archived tables or legacy sources.

        return ['ok' => true, 'usedNumber' => null, 'source' => null];
    }

    /**
     * Compute usage stats for a cashier.
     *
     * @param Cashier $cashier
     * @return array
     */
    public function computeStats(Cashier $cashier): array
    {
        $orStats = $this->computeTypeStats('or', $cashier->or_start, $cashier->or_end, $cashier->or_current);
        $invoiceStats = $this->computeTypeStats('invoice', $cashier->invoice_start, $cashier->invoice_end, $cashier->invoice_current);

        return [
            'id' => (int)$cashier->intID,
            'or' => $orStats,
            'invoice' => $invoiceStats,
        ];
    }

    /**
     * Helper: compute stats for a specific type/range.
     *
     * @param 'or'|'invoice' $type
     * @param int|null $start
     * @param int|null $end
     * @param int|null $current
     * @return array
     */
    protected function computeTypeStats(string $type, ?int $start, ?int $end, ?int $current): array
    {
        $start = $start ?? 0;
        $end = $end ?? 0;
        $current = $current ?? 0;

        $usedCount = 0;
        $lastUsed = null;

        if ($start > 0 && $end > 0 && $end >= $start) {
            $table = 'payment_details';
            $col = null;

            if ($type === 'invoice') {
                if (Schema::hasColumn($table, 'invoice_number')) {
                    $col = 'invoice_number';
                }
            } else {
                if (Schema::hasColumn($table, 'or_no')) {
                    $col = 'or_no';
                } elseif (Schema::hasColumn($table, 'or_number')) {
                    $col = 'or_number';
                }
            }

            if ($col) {
                $usedCount = (int) DB::table($table)
                    ->whereNotNull($col)
                    ->whereBetween($col, [$start, $end])
                    ->count();

                $lastRow = DB::table($table)
                    ->select($col)
                    ->whereNotNull($col)
                    ->whereBetween($col, [$start, $end])
                    ->orderBy($col, 'desc')
                    ->first();

                if ($lastRow && isset($lastRow->{$col})) {
                    $lastUsed = (int) $lastRow->{$col};
                }
            }
        }

        $remaining = 0;
        if ($start > 0 && $end > 0 && $current > 0 && $end >= $current) {
            $remaining = $end - $current + 1;
        }

        return [
            'start' => $start ?: null,
            'end' => $end ?: null,
            'current' => $current ?: null,
            'used_count' => $usedCount,
            'remaining_count' => $remaining,
            'last_used' => $lastUsed,
        ];
    }

    /**
     * Set current pointers to the start when ranges change (auto-reset policy).
     *
     * @param Cashier $cashier
     * @param bool $resetOr
     * @param bool $resetInvoice
     * @return Cashier
     */
    public function autoResetCurrents(Cashier $cashier, bool $resetOr = true, bool $resetInvoice = true): Cashier
    {
        if ($resetOr && !is_null($cashier->or_start)) {
            $cashier->or_current = $cashier->or_start;
        }
        if ($resetInvoice && !is_null($cashier->invoice_start)) {
            $cashier->invoice_current = $cashier->invoice_start;
        }
        $cashier->save();

        return $cashier;
    }

    /**
     * Utility to ensure current remains within [start, end].
     *
     * @param int $current
     * @param int $start
     * @param int $end
     * @return bool
     */
    public function currentWithinRange(int $current, int $start, int $end): bool
    {
        if ($start <= 0 || $end <= 0 || $end < $start) {
            return false;
        }
        return $current >= $start && $current <= $end;
    }
}
