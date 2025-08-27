<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\UserContextResolver;

class SystemLogService
{
    /**
     * Log a system action.
     *
     * @param string $action   One of: create, update, delete
     * @param string $entity   Entity name e.g. "Campus"
     * @param int|null $entityId Entity id if available
     * @param mixed $oldValues  Array/object of previous values (or null)
     * @param mixed $newValues  Array/object of new values (or null)
     * @param Request|null $request Laravel request for metadata (optional)
     */
    public static function log(string $action, string $entity, ?int $entityId, $oldValues, $newValues, ?Request $request = null): void
    {
        try {
            $userId = null;

            try {
                $userId = Auth::check() ? Auth::id() : null;
            } catch (Throwable $e) {
                // auth might not be configured in some contexts
                $userId = null;
            }

            // Fallback to CI session resolver if Laravel Auth is not set
            if (!$userId && $request instanceof Request) {
                try {
                    $resolver = app(UserContextResolver::class);
                    $resolved = $resolver->resolveUserId($request);
                    if ($resolved !== null) {
                        $userId = $resolved;
                    }
                } catch (Throwable $e) {
                    // ignore resolver errors
                }
            }

            // Additional fallback: use X-Faculty-ID (or request faculty_id) when present
            if (!$userId && $request instanceof Request) {
                $fid = $request->header('X-Faculty-ID', $request->input('faculty_id'));
                if ($fid !== null && is_numeric($fid)) {
                    $userId = (int) $fid;
                }
            }

            $ip = null;
            $agent = null;
            $method = null;
            $path = null;

            if ($request instanceof Request) {
                $ip = $request->ip();
                $agent = $request->header('User-Agent');
                $method = $request->method();
                $path = $request->path();
            }

            // Normalize values to arrays if model instances are passed
            if (is_object($oldValues) && method_exists($oldValues, 'toArray')) {
                $oldValues = $oldValues->toArray();
            }
            if (is_object($newValues) && method_exists($newValues, 'toArray')) {
                $newValues = $newValues->toArray();
            }

            SystemLog::create([
                'user_id'    => $userId,
                'entity'     => $entity,
                'entity_id'  => $entityId,
                'action'     => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $ip,
                'user_agent' => $agent,
                'method'     => $method,
                'path'       => $path,
            ]);
        } catch (Throwable $e) {
            // Do not break primary flow if logging fails; record to app log instead
            try {
                Log::error('SystemLogService logging failed: '.$e->getMessage(), [
                    'action' => $action,
                    'entity' => $entity,
                    'entity_id' => $entityId,
                ]);
            } catch (Throwable $ignored) {
                // Swallow secondary errors
            }
        }
    }
}
