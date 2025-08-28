<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GradingWindowService
{
    /**
     * Return window info for a given term and optional classlist.
     * - Uses tb_mas_sy.midterm_start/midterm_end and final_start/final_end
     * - Considers the latest tb_mas_sy_grading_extension row by date (DESC) as the active extension set
     * - Considers an extension active for a classlist if a row exists in tb_mas_sy_grading_extension_faculty
     *   with classlist_id and grading_extension_id referencing that latest extension
     *
     * @param int $syId tb_mas_sy.intID
     * @param int|null $classlistId tb_mas_classlist.intID
     * @return array
     */
    public function windowInfo(int $syId, ?int $classlistId = null): array
    {
        $today = date('Y-m-d');

        $sy = DB::table('tb_mas_sy')->where('intID', $syId)->first();

        $midtermStart = $this->normalizeDate($sy->midterm_start ?? null);
        $midtermEnd   = $this->normalizeDate($sy->midterm_end ?? null);
        $finalStart   = $this->normalizeDate($sy->final_start ?? null);
        $finalEnd     = $this->normalizeDate($sy->final_end ?? null);

        $midtermActive = $this->isInRange($today, $midtermStart, $midtermEnd);
        $finalActive   = $this->isInRange($today, $finalStart, $finalEnd);

        // Resolve latest extension set (by date desc)
        $latestExt = DB::table('tb_mas_sy_grading_extension')
            ->orderBy('date', 'DESC')
            ->first();

        $extensionActive = false;
        if ($latestExt && $classlistId) {
            $extRow = DB::table('tb_mas_sy_grading_extension_faculty')
                ->where('classlist_id', $classlistId)
                ->where('grading_extension_id', $latestExt->id)
                ->first();
            $extensionActive = $extRow ? true : false;
        }

        return [
            'now' => $today,
            'midterm_active' => $midtermActive,
            'final_active'   => $finalActive,
            'extension_active' => $extensionActive,
            'midterm' => [
                'start' => $midtermStart,
                'end'   => $midtermEnd,
            ],
            'final' => [
                'start' => $finalStart,
                'end'   => $finalEnd,
            ],
            'latest_extension' => $latestExt ? [
                'id'   => (int) $latestExt->id,
                'date' => (string) ($latestExt->date ?? ''),
            ] : null,
        ];
    }

    /**
     * Determine if a user with $role can edit for the given period.
     * Rules:
     * - registrar/admin: always allow (bypass windows)
     * - faculty: allowed if:
     *    * within term window for the period, OR
     *    * extension_active is true for the classlist (latest extension set)
     *
     * @param string $period 'midterm'|'finals'
     * @param int $syId
     * @param int|null $classlistId
     * @param string|null $role e.g. 'faculty'|'registrar'|'admin' (optional; when null, default to strict window enforcement)
     * @return bool
     */
    public function canEditPeriod(string $period, int $syId, ?int $classlistId = null, ?string $role = null): bool
    {
        $role = $role ? strtolower(trim($role)) : null;

        if (in_array($role, ['registrar', 'admin'], true)) {
            return true;
        }

        $info = $this->windowInfo($syId, $classlistId);

        if ($info['extension_active'] === true) {
            return true;
        }

        if ($period === 'midterm') {
            return $info['midterm_active'] === true;
        }

        if ($period === 'finals') {
            return $info['final_active'] === true;
        }

        // Unknown period -> deny
        return false;
    }

    /**
     * Normalize incoming date values.
     * Accepts null, empty strings, '0000-00-00' and returns null in those cases.
     *
     * @param mixed $val
     * @return string|null YYYY-MM-DD or null
     */
    protected function normalizeDate($val): ?string
    {
        if (!$val) {
            return null;
        }
        $v = trim((string) $val);
        if ($v === '' || $v === '0000-00-00' || $v === '1970-01-01') {
            return null;
        }
        // Keep YYYY-MM-DD
        return substr($v, 0, 10);
    }

    /**
     * Check if $today is in [$start, $end] inclusive. Null start/end means not active.
     *
     * @param string $today YYYY-MM-DD
     * @param string|null $start
     * @param string|null $end
     * @return bool
     */
    protected function isInRange(string $today, ?string $start, ?string $end): bool
    {
        if (!$start || !$end) {
            return false;
        }
        return $today >= $start && $today <= $end;
    }
}
