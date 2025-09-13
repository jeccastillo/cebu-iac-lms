<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate for grading systems management (admin or faculty_admin)
        Gate::define('grading.manage', function ($user) {
            try {
                if (is_object($user)) {
                    // Prefer Faculty role checks if available
                    if (method_exists($user, 'hasAnyRole')) {
                        return $user->hasAnyRole(['admin', 'faculty_admin']);
                    }
                    if (method_exists($user, 'hasRole')) {
                        return $user->hasRole('admin') || $user->hasRole('faculty_admin');
                    }
                    // Fallback if a roles accessor is available
                    if (isset($user->role_codes)) {
                        $codes = array_map('strtolower', (array) $user->role_codes);
                        return in_array('admin', $codes, true) || in_array('faculty_admin', $codes, true);
                    }
                }
            } catch (\Throwable $e) {
                return false;
            }
            return false;
        });

        // Gate: grade.classlist.edit — registrar/admin can edit any; faculty can edit only if assigned to the classlist
        Gate::define('grade.classlist.edit', function ($user, int $classlistId) {
            try {
                if (!$user) return false;
                // Admin/Registrar bypass
                if (self::userHasAnyRole($user, ['admin', 'registrar'])) return true;

                // Faculty: must be assigned to the classlist
                $uid = (int) ($user->intID ?? 0);
                if ($uid <= 0) return false;

                $cl = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
                if (!$cl) return false;

                return (int) ($cl->intFacultyID ?? 0) === $uid;
            } catch (\Throwable $e) {
                return false;
            }
        });

        // Gate: grade.classlist.finalize — same as edit (faculty must be assigned) OR registrar/admin
        Gate::define('grade.classlist.finalize', function ($user, int $classlistId) {
            try {
                if (!$user) return false;
                if (self::userHasAnyRole($user, ['admin', 'registrar'])) return true;

                $uid = (int) ($user->intID ?? 0);
                if ($uid <= 0) return false;

                $cl = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
                if (!$cl) return false;

                return (int) ($cl->intFacultyID ?? 0) === $uid;
            } catch (\Throwable $e) {
                return false;
            }
        });

        // Gate: grade.classlist.unfinalize — registrar/admin only
        Gate::define('grade.classlist.unfinalize', function ($user, int $classlistId) {
            try {
                if (!$user) return false;
                return self::userHasAnyRole($user, ['admin', 'registrar']);
            } catch (\Throwable $e) {
                return false;
            }
        });

        // ------------------------------
        // Attendance gates (view/edit)
        // Policy: assigned faculty OR admin
        // ------------------------------
        Gate::define('attendance.classlist.view', function ($user, int $classlistId) {
            try {
                if (!$user) return false;
                if (self::userHasAnyRole($user, ['admin'])) return true;

                $uid = (int) ($user->intID ?? 0);
                if ($uid <= 0) return false;

                $cl = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
                if (!$cl) return false;

                return (int) ($cl->intFacultyID ?? 0) === $uid;
            } catch (\Throwable $e) {
                return false;
            }
        });

        Gate::define('attendance.classlist.edit', function ($user, int $classlistId) {
            try {
                if (!$user) return false;
                if (self::userHasAnyRole($user, ['admin'])) return true;

                $uid = (int) ($user->intID ?? 0);
                if ($uid <= 0) return false;

                $cl = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
                if (!$cl) return false;

                return (int) ($cl->intFacultyID ?? 0) === $uid;
            } catch (\Throwable $e) {
                return false;
            }
        });
    }

    /**
     * Helper: detect if a user has any of the given roles.
     * Supports:
     *  - $user->hasAnyRole(array)
     *  - $user->hasRole(string)
     *  - $user->role_codes (array or scalar)
     */
    protected static function userHasAnyRole($user, array $roles): bool
    {
        try {
            if (!$user) {
                return false;
            }
            // Normalize target roles
            $want = array_map('strtolower', array_map('trim', $roles));

            // 1) hasAnyRole(array)
            if (method_exists($user, 'hasAnyRole')) {
                if ($user->hasAnyRole($roles)) {
                    return true;
                }
            }

            // 2) hasRole(string)
            if (method_exists($user, 'hasRole')) {
                foreach ($want as $r) {
                    if ($user->hasRole($r)) {
                        return true;
                    }
                }
            }

            // 3) role_codes property/attribute
            if (isset($user->role_codes)) {
                $codes = (array) $user->role_codes;
                $codes = array_map('strtolower', array_map('trim', $codes));
                foreach ($want as $r) {
                    if (in_array($r, $codes, true)) {
                        return true;
                    }
                }
            }

            // 4) generic roles property (array of strings)
            if (isset($user->roles)) {
                $codes = (array) $user->roles;
                $codes = array_map('strtolower', array_map('trim', $codes));
                foreach ($want as $r) {
                    if (in_array($r, $codes, true)) {
                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            return false;
        }
        return false;
    }
}
