<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemAlert;
use App\Models\SystemAlertRead;
use App\Services\SystemAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SystemAlertController extends Controller
{
    protected SystemAlertService $svc;

    public function __construct(SystemAlertService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * Admin list of alerts with optional filters.
     * GET /api/v1/system-alerts
     */
    public function index(Request $request)
    {
        $q = SystemAlert::query();

        // Filters
        if ($request->has('active')) {
            $active = (int) $request->query('active');
            $q->where('intActive', $active ? 1 : 0);
        }
        if ($request->has('system_generated')) {
            $q->where('system_generated', (int) $request->query('system_generated') ? 1 : 0);
        }
        if ($request->has('role_code')) {
            $code = strtolower(trim((string) $request->query('role_code')));
            if ($code !== '') {
                $q->whereRaw('JSON_CONTAINS(LOWER(role_codes), JSON_QUOTE(?))', [$code]);
            }
        }
        if ($request->has('campus_id')) {
            $campusId = (int) $request->query('campus_id');
            $q->whereRaw('JSON_CONTAINS(campus_ids, ?)', [json_encode($campusId)]);
        }

        $q->orderByDesc('created_at');

        $data = $q->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $data->items(),
            'meta'    => [
                'total' => $data->total(),
                'page'  => $data->currentPage(),
                'pages' => $data->lastPage(),
            ],
        ]);
    }

    /**
     * Create an alert (admin).
     * POST /api/v1/system-alerts
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'nullable|string|max:255',
            'message'          => 'required|string',
            'link'             => 'nullable|string|max:2048',
            'type'             => 'required|in:success,warning,error,info',
            'target_all'       => 'sometimes|boolean',
            'role_codes'       => 'array',
            'role_codes.*'     => 'string',
            'campus_ids'       => 'array',
            'campus_ids.*'     => 'integer',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'intActive'        => 'sometimes|integer|in:0,1',
            'system_generated' => 'sometimes|boolean',
        ]);

        // Normalize role codes to lowercase
        if (isset($data['role_codes']) && is_array($data['role_codes'])) {
            $data['role_codes'] = array_values(array_unique(array_map(function ($r) {
                return strtolower(trim((string) $r));
            }, $data['role_codes'])));
        }

        // Default flags
        $data['intActive'] = (int) ($data['intActive'] ?? 1);
        $data['system_generated'] = (int) ($data['system_generated'] ?? 0);

        // Audit: created_by (optional; no auth user context guaranteed)
        $data['created_by'] = null;

        $alert = SystemAlert::create($data);

        // Broadcast
        $this->svc->broadcast('create', $alert);

        return response()->json([
            'success' => true,
            'data'    => $alert->fresh(),
        ], 201);
    }

    /**
     * Update an alert (admin).
     * PUT /api/v1/system-alerts/{id}
     */
    public function update(Request $request, int $id)
    {
        $alert = SystemAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
        }

        $data = $request->validate([
            'title'            => 'nullable|string|max:255',
            'message'          => 'sometimes|string',
            'link'             => 'nullable|string|max:2048',
            'type'             => 'sometimes|in:success,warning,error,info',
            'target_all'       => 'sometimes|boolean',
            'role_codes'       => 'sometimes|array',
            'role_codes.*'     => 'string',
            'campus_ids'       => 'sometimes|array',
            'campus_ids.*'     => 'integer',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'intActive'        => 'sometimes|integer|in:0,1',
            'system_generated' => 'sometimes|boolean',
        ]);

        if (array_key_exists('role_codes', $data) && is_array($data['role_codes'])) {
            $data['role_codes'] = array_values(array_unique(array_map(function ($r) {
                return strtolower(trim((string) $r));
            }, $data['role_codes'])));
        }

        $alert->update($data);

        // Broadcast
        $this->svc->broadcast('update', $alert);

        return response()->json([
            'success' => true,
            'data'    => $alert->fresh(),
        ]);
    }

    /**
     * Disable (soft delete) an alert by default. Use ?hard=1 for hard delete.
     * DELETE /api/v1/system-alerts/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $alert = SystemAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
        }

        $hard = (int) $request->query('hard', 0) === 1;

        if ($hard) {
            $alert->delete();
            $this->svc->broadcast('delete', $alert);
        } else {
            $alert->intActive = 0;
            $alert->save();
            $this->svc->broadcast('update', $alert);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Active alerts for current audience (roles/campus), excluding dismissed.
     * GET /api/v1/system-alerts/active
     */
    public function active(Request $request)
    {
        $roles = $this->svc->rolesFromRequest($request);                
        $campusId = $this->svc->campusFromRequest($request);
        $userIdentifier = $this->svc->userIdentifier($request);
        $now = now();

        // Candidate list: active rows; time window roughly filtered in DB then refined
        $candidate = SystemAlert::query()
            ->where('intActive', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('created_at')
            ->get();

        $filtered = $this->svc->filterForAudience($candidate, [
            'roles'     => $roles,
            'campus_id' => $campusId,
            'now'       => $now,
        ]);

        $final = $this->svc->excludeDismissed($filtered, $userIdentifier);

        return response()->json([
            'success' => true,
            'data'    => array_values(array_map(function ($a) {
                return $a->toArray();
            }, $final)),
        ]);
    }

    /**
     * Dismiss an alert for the current user (idempotent).
     * POST /api/v1/system-alerts/{id}/dismiss
     */
    public function dismiss(Request $request, int $id)
    {
        $alert = SystemAlert::find($id);
        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
        }

        $userIdentifier = $this->svc->userIdentifier($request);
        $userId = $request->header('X-User-ID');
        $loginType = $request->header('X-Login-Type');
        $campusId = $this->svc->campusFromRequest($request);

        // Upsert read
        $now = Carbon::now();
        try {
            SystemAlertRead::updateOrCreate(
                [
                    'alert_id'       => $alert->id,
                    'user_identifier'=> $userIdentifier,
                ],
                [
                    'user_id'        => $userId !== null && $userId !== '' ? (int) $userId : null,
                    'login_type'     => $loginType ? (string) $loginType : null,
                    'campus_id'      => $campusId,
                    'dismissed_at'   => $now,
                ]
            );
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Dismiss failed: '.$e->getMessage()], 422);
        }

        return response()->json(['success' => true]);
    }
}
