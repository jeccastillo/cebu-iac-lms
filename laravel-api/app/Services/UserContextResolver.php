<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserContextResolver
{
    /**
     * Resolve user ID from multiple authentication sources
     *
     * @param Request|null $request
     * @return int|null
     */
    public function resolveUserId(?Request $request = null): ?int
    {
        // Try Laravel Auth first (fastest)
        $userId = $this->resolveFromLaravelAuth();
        if ($userId !== null) {
            return $userId;
        }

        // Try CodeIgniter session
        $userId = $this->resolveFromCodeIgniterSession();
        if ($userId !== null) {
            return $userId;
        }

        // Try request headers (for API authentication)
        if ($request) {
            $userId = $this->resolveFromRequestHeaders($request);
            if ($userId !== null) {
                return $userId;
            }
        }

        // Try database session validation as last resort
        $userId = $this->resolveFromDatabase();
        if ($userId !== null) {
            return $userId;
        }

        return null;
    }

    /**
     * Resolve user type from authentication sources
     *
     * @param Request|null $request
     * @return string|null
     */
    public function resolveUserType(?Request $request = null): ?string
    {
        // Check Laravel Auth first
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof Faculty) {
                return 'faculty';
            } elseif ($user instanceof Student) {
                return 'student';
            }
        }

        // Check CodeIgniter session
        $userType = $this->resolveUserTypeFromCodeIgniterSession();
        if ($userType !== null) {
            return $userType;
        }

        return null;
    }

    /**
     * Resolve user ID from Laravel Auth
     *
     * @return int|null
     */
    protected function resolveFromLaravelAuth(): ?int
    {
        try {
            return Auth::check() ? Auth::id() : null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve user from Laravel Auth', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Resolve user ID from CodeIgniter session
     *
     * @return int|null
     */
    protected function resolveFromCodeIgniterSession(): ?int
    {
        try {
            // Check if session is started
            if (session_status() !== PHP_SESSION_ACTIVE) {
                return null;
            }

            // Check for faculty session
            if (isset($_SESSION['faculty_logged_in']) && $_SESSION['faculty_logged_in'] === true) {
                return isset($_SESSION['faculty_id']) ? (int) $_SESSION['faculty_id'] : null;
            }

            // Check for student session
            if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
                return isset($_SESSION['student_id']) ? (int) $_SESSION['student_id'] : null;
            }

            // Check for general user session (legacy)
            if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
                return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve user from CodeIgniter session', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Resolve user type from CodeIgniter session
     *
     * @return string|null
     */
    protected function resolveUserTypeFromCodeIgniterSession(): ?string
    {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                return null;
            }

            if (isset($_SESSION['faculty_logged_in']) && $_SESSION['faculty_logged_in'] === true) {
                return 'faculty';
            }

            if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
                return 'student';
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve user type from CodeIgniter session', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Resolve user ID from request headers
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveFromRequestHeaders(Request $request): ?int
    {
        try {
            // Check for custom user ID header (primary)
            $userId = $request->header('X-User-ID');
            if ($userId && is_numeric($userId)) {
                return (int) $userId;
            }

            // Fallback: allow X-Faculty-ID for legacy admin requests
            // Note: Some clients only send X-Faculty-ID. In instances where downstream expects tb_mas_users.intID,
            // systems without a mapping will still use this value to stamp actions.
            $facultyId = $request->header('X-Faculty-ID');
            if ($facultyId && is_numeric($facultyId)) {
                return (int) $facultyId;
            }

            // Check for authorization header with user context
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                // This would require token validation logic
                // For now, we'll skip this implementation
                return null;
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve user from request headers', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Resolve user ID from database session validation
     *
     * @return int|null
     */
    protected function resolveFromDatabase(): ?int
    {
        try {
            // Get session ID from current session
            $sessionId = session_id();
            if (!$sessionId) {
                return null;
            }

            // Check if there's a session table in the database
            // This is a fallback mechanism for session validation
            $session = DB::table('ci_sessions')
                ->where('id', $sessionId)
                ->where('timestamp >', time() - 3600) // 1 hour expiry
                ->first();

            if ($session && isset($session->data)) {
                // Parse session data to extract user ID
                $sessionData = unserialize($session->data);
                if (is_array($sessionData)) {
                    if (isset($sessionData['faculty_id'])) {
                        return (int) $sessionData['faculty_id'];
                    }
                    if (isset($sessionData['student_id'])) {
                        return (int) $sessionData['student_id'];
                    }
                    if (isset($sessionData['user_id'])) {
                        return (int) $sessionData['user_id'];
                    }
                }
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve user from database session', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get comprehensive user context
     *
     * @param Request|null $request
     * @return array
     */
    public function getUserContext(?Request $request = null): array
    {
        return [
            'user_id' => $this->resolveUserId($request),
            'user_type' => $this->resolveUserType($request),
            'resolved_from' => $this->getResolutionSource($request),
        ];
    }

    /**
     * Get the source from which user was resolved
     *
     * @param Request|null $request
     * @return string|null
     */
    protected function getResolutionSource(?Request $request = null): ?string
    {
        if ($this->resolveFromLaravelAuth() !== null) {
            return 'laravel_auth';
        }

        if ($this->resolveFromCodeIgniterSession() !== null) {
            return 'codeigniter_session';
        }

        if ($request && $this->resolveFromRequestHeaders($request) !== null) {
            return 'request_headers';
        }

        if ($this->resolveFromDatabase() !== null) {
            return 'database_session';
        }

        return null;
    }
}
