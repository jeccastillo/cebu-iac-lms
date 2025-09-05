<?php

namespace App\Services;

use App\Events\SystemAlertBroadcasted;
use App\Models\SystemAlert;
use App\Models\SystemAlertRead;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SystemAlertService
{
    /**
     * Build a stable per-user identifier for dismissal persistence.
     * Prefer username|loginType from headers; fallback to user_id|loginType; else 'guest|unknown'.
     */
    public function userIdentifier(Request $request): string
    {
        $username  = strtolower(trim((string) $request->header('X-User-Name', '')));
        $loginType = strtolower(trim((string) $request->header('X-Login-Type', '')));

        if ($username !== '' && $loginType !== '') {
            return $username . '|' . $loginType;
        }

        // Fallback to numeric user id if provided
        $userId = (string) $request->header('X-User-ID', '');
        if ($userId !== '' && $loginType !== '') {
            return strtolower($userId) . '|' . $loginType;
        }

        // Last resort
        return 'guest|unknown';
    }

    /**
     * Extract roles array from request header 'X-User-Roles' (comma or space separated).
     */
    public function rolesFromRequest(Request $request): array
    {
        $hdr = (string) ($request->header('X-User-Roles') ?? '');
        if ($hdr === '') return [];
        // Support both comma-separated and space-separated
        $raw = preg_split('/[,\s]+/', $hdr) ?: [];
        $roles = array_values(array_filter(array_map(function ($r) {
            return strtolower(trim((string) $r));
        }, $raw), function ($r) {
            return $r !== '';
        }));
        // de-duplicate
        return array_values(array_unique($roles));
    }

    /**
     * Extract campus id (int) from header X-Campus-ID (optional).
     */
    public function campusFromRequest(Request $request): ?int
    {
        $val = $request->header('X-Campus-ID');
        if ($val === null || $val === '') return null;
        $n = (int) $val;
        return is_nan($n) ? null : $n;
    }

    /**
     * Filter alerts by audience: role intersection OR campus match OR target_all, plus active window and intActive=1.
     *
     * @param Collection|array<int,SystemAlert> $alerts
     * @param array $ctx ['roles'=>string[], 'campus_id'=>?int, 'now'=>Carbon]
     * @return array<int,SystemAlert>
     */
    public function filterForAudience($alerts, array $ctx): array
    {
        $roles = array_values(array_unique(array_map('strtolower', (array) ($ctx['roles'] ?? []))));
        $campusId = $ctx['campus_id'] ?? null;
        $now = $ctx['now'] ?? now();

        $list = [];
        foreach ($alerts as $a) {
            if ((int) ($a->intActive ?? 0) !== 1) continue;
            // time window
            if ($a->starts_at && Carbon::parse($a->starts_at)->gt($now)) continue;
            if ($a->ends_at && Carbon::parse($a->ends_at)->lt($now)) continue;

            if ($a->target_all) {
                $list[] = $a;
                continue;
            }

            $okByRole = false;
            $okByCampus = false;

            $alertRoles = array_map('strtolower', (array) ($a->role_codes ?? []));            
            if (!empty($alertRoles)) {
                // intersection
                $intersect = $alertRoles;
                $okByRole = count($intersect) > 0;
            }

            $alertCampuses = (array) ($a->campus_ids ?? []);
            if ($campusId !== null && !empty($alertCampuses)) {
                $okByCampus = in_array((int) $campusId, array_map('intval', $alertCampuses), true);
            }

            if ($okByRole || $okByCampus) {
                $list[] = $a;
            }
        }

        return $list;
    }

    /**
     * Exclude alerts dismissed by this user (based on userIdentifier).
     *
     * @param array<int,SystemAlert> $alerts
     * @param string $userIdentifier
     * @return array<int,SystemAlert>
     */
    public function excludeDismissed(array $alerts, string $userIdentifier): array
    {
        if (empty($alerts)) return [];

        $ids = array_values(array_map(function ($a) { return (int) $a->id; }, $alerts));
        $reads = SystemAlertRead::query()
            ->whereIn('alert_id', $ids)
            ->where('user_identifier', $userIdentifier)
            ->pluck('alert_id')
            ->all();

        if (empty($reads)) return $alerts;

        $dismissed = array_flip(array_map('intval', $reads));
        return array_values(array_filter($alerts, function ($a) use ($dismissed) {
            return !isset($dismissed[(int) $a->id]);
        }));
    }

    /**
     * Broadcast alert lifecycle updates (create, update, delete).
     * Channel selection is handled by the event; here we just dispatch.
     */
    public function broadcast(string $action, SystemAlert $alert): void
    {
        try {
            event(new SystemAlertBroadcasted($action, $alert));
        } catch (\Throwable $e) {
            // Swallow broadcasting failures; feature should degrade gracefully to polling.
            \Log::warning('SystemAlert broadcast failed: ' . $e->getMessage());
        }
    }
}
