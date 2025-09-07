<?php

namespace App\Http\Middleware;

use App\Models\Faculty;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Enforce that the current faculty context has any of the required roles.
     *
     * Usage in routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:registrar,admin')
     *
     * Faculty context resolution (temporary until auth tokens are wired):
     * - Header: X-Faculty-ID
     * - Fallback: request input faculty_id
     */
    public function handle(Request $request, Closure $next, ...$rolesCsv): Response
    {
        $required = array_values(array_filter(array_map('trim', explode(',', implode(',', $rolesCsv)))));
        if (empty($required)) {
            return response()->json([
                'success' => false,
                'message' => 'Role middleware misconfigured (no roles specified)'
            ], 500);
        }

        // First: allow authenticated session users to pass if they have any required role
        try {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole($required)) {
                    // Expose faculty model when applicable for downstream consumers
                    if ($user instanceof \App\Models\Faculty) {
                        $request->attributes->set('faculty', $user);
                    }
                    return $next($request);
                }
            }
        } catch (\Throwable $e) {
            // fall through to header-based resolution
        }

        // Resolve faculty id (temporary dev approach)
        $facultyId = $request->headers->get('X-Faculty-ID', $request->input('faculty_id'));

        if (!$facultyId) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty context required (provide X-Faculty-ID header)'
            ], 401);
        }

        $faculty = Faculty::find($facultyId);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty not found'
            ], 401);
        }

        if (!$faculty->hasAnyRole($required)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - missing required role'
            ], 403);
        }

        // Make the faculty model available to downstream handlers if needed
        $request->attributes->set('faculty', $faculty);

        return $next($request);
    }
}
