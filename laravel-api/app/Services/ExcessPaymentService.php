<?php

namespace App\Services;

use App\Models\ExcessPaymentApplication;
use Illuminate\Support\Facades\DB;

class ExcessPaymentService
{
    protected StudentLedgerService $ledger;

    public function __construct(StudentLedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * Apply an excess (negative closing) from a source term as a payment to a target term.
     *
     * Validations:
     * - amount > 0
     * - source_term_id != target_term_id
     * - source term effective closing (including prior transfers) <= -amount (sufficient credit)
     * - target term effective closing (including prior transfers) >= amount (has due to apply against)
     *
     * @return array Application row as array
     *
     * @throws \InvalidArgumentException on invalid payload
     */
    public function applyExcessPayment(
        int $studentId,
        int $sourceTermId,
        int $targetTermId,
        float $amount,
        ?int $actorId = null,
        ?string $notes = null
    ): array {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }
        if ($sourceTermId === $targetTermId) {
            throw new \InvalidArgumentException('Source term and target term must be different.');
        }

        // Compute effective closings including previously applied (not reverted) transfers
        $sourceClosing = $this->getTermClosing($studentId, $sourceTermId);
        $targetClosing = $this->getTermClosing($studentId, $targetTermId);

        $alreadyOut = $this->sumAppliedOut($studentId, $sourceTermId);
        $alreadyIn  = $this->sumAppliedIn($studentId, $targetTermId);

        // For source: closing is negative for excess; transfers out increase assessment (less negative)
        $effectiveSourceClosing = $sourceClosing + $alreadyOut;
        $sourceAvailable = abs(min(0.0, $effectiveSourceClosing)); // available positive credit to apply

        // For target: closing is positive when due; transfers in increase payment (less positive)
        $effectiveTargetClosing = $targetClosing - $alreadyIn;
        $targetDue = max(0.0, $effectiveTargetClosing);

        $maxTransferable = min($sourceAvailable, $targetDue);

        if ($sourceAvailable <= 0.0) {
            throw new \InvalidArgumentException('No available excess credit in the source term.');
        }
        if ($targetDue <= 0.0) {
            throw new \InvalidArgumentException('Target term has no outstanding due to apply against.');
        }
        if ($amount - $maxTransferable > 0.00001) {
            throw new \InvalidArgumentException('Requested amount exceeds allowed bounds. Max transferable is ' . number_format($maxTransferable, 2));
        }

        $app = DB::transaction(function () use ($studentId, $sourceTermId, $targetTermId, $amount, $actorId, $notes) {
            $row = new ExcessPaymentApplication();
            $row->student_id     = $studentId;
            $row->source_term_id = $sourceTermId;
            $row->target_term_id = $targetTermId;
            $row->amount         = round($amount, 2);
            $row->status         = 'applied';
            $row->created_by     = $actorId;
            $row->notes          = $notes;
            $row->save();

            return $row;
        });

        return $app->toArray();
    }

    /**
     * Revert an applied excess payment application.
     *
     * @return array Updated application as array
     *
     * @throws \InvalidArgumentException when not found or already reverted
     */
    public function revertExcessPayment(int $applicationId, ?int $actorId = null, ?string $notes = null): array
    {
        /** @var ExcessPaymentApplication|null $app */
        $app = ExcessPaymentApplication::find($applicationId);
        if (!$app) {
            throw new \InvalidArgumentException('Application not found.');
        }
        if ($app->status !== 'applied') {
            throw new \InvalidArgumentException('Only applied applications can be reverted.');
        }

        DB::transaction(function () use ($app, $actorId, $notes) {
            $app->status = 'reverted';
            $app->reverted_by = $actorId;
            $app->reverted_at = now();
            if ($notes) {
                $app->notes = trim(($app->notes ? ($app->notes . PHP_EOL) : '') . '[REVERT] ' . $notes);
            }
            $app->save();
        });

        return $app->toArray();
    }

    /**
     * Augment a ledger array with excess application virtual rows (applied only).
     * Injects:
     * - Source term: assessment += amount (ref_type: excess_transfer_out)
     * - Target term: payment   += amount (ref_type: excess_transfer_in)
     *
     * Recomputes meta totals.
     *
     * @param array $ledger The ledger returned by StudentLedgerService::getLedger
     * @param string $sort 'asc'|'desc' default 'asc'
     * @return array
     */
    public function augmentLedger(array $ledger, string $sort = 'asc'): array
    {
        $studentId = (int) ($ledger['student_id'] ?? 0);
        if (!$studentId) {
            return $ledger;
        }

        $scopeTerm = $ledger['scope']['term'] ?? 'all';
        $appsQuery = ExcessPaymentApplication::query()
            ->where('student_id', $studentId)
            ->where('status', 'applied');

        if ($scopeTerm !== 'all' && is_numeric($scopeTerm)) {
            $termId = (int)$scopeTerm;
            $appsQuery->where(function ($q) use ($termId) {
                $q->where('source_term_id', $termId)
                  ->orWhere('target_term_id', $termId);
            });
        }

        $apps = $appsQuery->get();

        if ($apps->isEmpty()) {
            return $ledger;
        }

        $rows = is_array($ledger['rows'] ?? null) ? $ledger['rows'] : [];
        $injected = [];

        foreach ($apps as $app) {
            $createdAt = $app->created_at ? $app->created_at->format('Y-m-d H:i:s') : null;

            if ($scopeTerm === 'all' || (is_numeric($scopeTerm) && (int)$scopeTerm === (int)$app->source_term_id)) {
                $injected[] = [
                    'id'              => 'excess_out:' . $app->id . ':' . (int)$app->source_term_id,
                    'ref_type'        => 'excess_transfer_out',
                    'syid'            => (int)$app->source_term_id,
                    'sy_label'        => null,
                    'posted_at'       => $createdAt,
                    'or_no'           => null,
                    'invoice_number'  => null,
                    'assessment'      => (float)$app->amount,
                    'payment'         => null,
                    'cashier_id'      => null,
                    'cashier_name'    => null,
                    'remarks'         => 'Excess transfer to Term #' . (int)$app->target_term_id . ' (App #' . $app->id . ')',
                    'source'          => 'excess_application',
                    'source_id'       => (int)$app->id,
                ];
            }

            if ($scopeTerm === 'all' || (is_numeric($scopeTerm) && (int)$scopeTerm === (int)$app->target_term_id)) {
                $injected[] = [
                    'id'              => 'excess_in:' . $app->id . ':' . (int)$app->target_term_id,
                    'ref_type'        => 'excess_transfer_in',
                    'syid'            => (int)$app->target_term_id,
                    'sy_label'        => null,
                    'posted_at'       => $createdAt,
                    'or_no'           => null,
                    'invoice_number'  => null,
                    'assessment'      => null,
                    'payment'         => (float)$app->amount,
                    'cashier_id'      => null,
                    'cashier_name'    => null,
                    'remarks'         => 'Excess transfer from Term #' . (int)$app->source_term_id . ' (App #' . $app->id . ')',
                    'source'          => 'excess_application',
                    'source_id'       => (int)$app->id,
                ];
            }
        }

        $rows = array_merge($rows, $injected);

        // Sort by posted_at then id
        usort($rows, function ($a, $b) use ($sort) {
            $pa = $a['posted_at'] ?? '';
            $pb = $b['posted_at'] ?? '';
            if ($pa === $pb) {
                return $sort === 'desc'
                    ? strcmp((string)($b['id'] ?? ''), (string)($a['id'] ?? ''))
                    : strcmp((string)($a['id'] ?? ''), (string)($b['id'] ?? ''));
            }
            return $sort === 'desc' ? strcmp($pb, $pa) : strcmp($pa, $pb);
        });

        // Recompute meta totals
        $totA = 0.0;
        $totP = 0.0;
        foreach ($rows as $r) {
            if (isset($r['assessment']) && is_numeric($r['assessment'])) $totA += (float)$r['assessment'];
            if (isset($r['payment']) && is_numeric($r['payment'])) $totP += (float)$r['payment'];
        }
        $opening = (float)($ledger['meta']['opening_balance'] ?? 0.0);
        $closing = $opening + $totA - $totP;

        $ledger['rows'] = $rows;
        $ledger['meta']['total_assessment'] = round($totA, 2);
        $ledger['meta']['total_payment']    = round($totP, 2);
        $ledger['meta']['closing_balance']  = round($closing, 2);

        return $ledger;
    }

    protected function getTermClosing(int $studentId, int $termId): float
    {
        $lg = $this->ledger->getLedger(null, $studentId, $termId, 'asc');
        return (float) ($lg['meta']['closing_balance'] ?? 0.0);
    }

    protected function sumAppliedOut(int $studentId, int $termId): float
    {
        return (float) ExcessPaymentApplication::query()
            ->where('student_id', $studentId)
            ->where('status', 'applied')
            ->where('source_term_id', $termId)
            ->sum('amount');
    }

    protected function sumAppliedIn(int $studentId, int $termId): float
    {
        return (float) ExcessPaymentApplication::query()
            ->where('student_id', $studentId)
            ->where('status', 'applied')
            ->where('target_term_id', $termId)
            ->sum('amount');
    }
}
