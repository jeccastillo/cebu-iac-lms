<?php

namespace App\Guards;

use App\Models\Faculty;
use App\Models\Student;
use App\Services\UserContextResolver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CodeIgniterSessionGuard implements Guard
{
    /**
     * The user provider implementation.
     *
     * @var UserProvider
     */
    protected $provider;

    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The currently authenticated user.
     *
     * @var Authenticatable|null
     */
    protected $user;

    /**
     * User context resolver instance.
     *
     * @var UserContextResolver
     */
    protected $userContextResolver;

    /**
     * Create a new authentication guard.
     *
     * @param UserProvider $provider
     * @param Request $request
     * @param UserContextResolver $userContextResolver
     */
    public function __construct(UserProvider $provider, Request $request, UserContextResolver $userContextResolver)
    {
        $this->provider = $provider;
        $this->request = $request;
        $this->userContextResolver = $userContextResolver;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        // Return cached user if already resolved
        if ($this->user !== null) {
            return $this->user;
        }

        // Try to resolve user from CodeIgniter session
        $this->user = $this->resolveUserFromSession();

        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        if ($user = $this->user()) {
            return $user->getAuthIdentifier();
        }

        return null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        // This guard is read-only for existing CI sessions
        // Actual validation happens in CodeIgniter
        return $this->check();
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    /**
     * Set the current user.
     *
     * @param Authenticatable $user
     * @return $this
     */
    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Resolve user from CodeIgniter session
     *
     * @return Authenticatable|null
     */
    protected function resolveUserFromSession(): ?Authenticatable
    {
        try {
            // Check if session is active
            if (session_status() !== PHP_SESSION_ACTIVE) {
                return null;
            }

            // Try to resolve faculty user
            $facultyUser = $this->resolveFacultyUser();
            if ($facultyUser) {
                return $facultyUser;
            }

            // Try to resolve student user
            $studentUser = $this->resolveStudentUser();
            if ($studentUser) {
                return $studentUser;
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve user from CodeIgniter session in guard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Resolve faculty user from session
     *
     * @return Faculty|null
     */
    protected function resolveFacultyUser(): ?Faculty
    {
        try {
            if (isset($_SESSION['faculty_logged_in']) && $_SESSION['faculty_logged_in'] === true) {
                $facultyId = $_SESSION['faculty_id'] ?? null;
                if ($facultyId) {
                    $faculty = Faculty::find((int) $facultyId);
                    if ($faculty) {
                        Log::info('Faculty user resolved from CI session', [
                            'faculty_id' => $facultyId,
                            'faculty_name' => $faculty->strFirstname . ' ' . $faculty->strLastname
                        ]);
                        return $faculty;
                    }
                }
            }
            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve faculty user from session', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Resolve student user from session
     *
     * @return Student|null
     */
    protected function resolveStudentUser(): ?Student
    {
        try {
            if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
                $studentId = $_SESSION['student_id'] ?? null;
                if ($studentId) {
                    $student = Student::find((int) $studentId);
                    if ($student) {
                        Log::info('Student user resolved from CI session', [
                            'student_id' => $studentId,
                            'student_name' => $student->strFirstname . ' ' . $student->strLastname
                        ]);
                        return $student;
                    }
                }
            }

            // Also check for legacy user session (students)
            if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
                $userId = $_SESSION['user_id'] ?? null;
                if ($userId) {
                    $student = Student::find((int) $userId);
                    if ($student) {
                        Log::info('Student user resolved from legacy CI session', [
                            'user_id' => $userId,
                            'student_name' => $student->strFirstname . ' ' . $student->strLastname
                        ]);
                        return $student;
                    }
                }
            }

            return null;
        } catch (Throwable $e) {
            Log::warning('Failed to resolve student user from session', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get session data for debugging
     *
     * @return array
     */
    public function getSessionData(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return ['session_status' => 'inactive'];
        }

        return [
            'session_status' => 'active',
            'faculty_logged_in' => $_SESSION['faculty_logged_in'] ?? false,
            'student_logged_in' => $_SESSION['student_logged_in'] ?? false,
            'user_logged_in' => $_SESSION['user_logged_in'] ?? false,
            'faculty_id' => $_SESSION['faculty_id'] ?? null,
            'student_id' => $_SESSION['student_id'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
        ];
    }

    /**
     * Force refresh user from session
     *
     * @return Authenticatable|null
     */
    public function refreshUser(): ?Authenticatable
    {
        $this->user = null;
        return $this->user();
    }
}
