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

        // ------------------------------
        // Department deficiencies gates
        // ------------------------------
        Gate::define('department.deficiency.view', function ($user, string $departmentCode) {
            try {
                // Admin bypass: authenticated user role OR X-User-Roles header contains 'admin'
                if ($user && self::userHasAnyRole($user, ['admin'])) return true;
                $rolesHdr = (string) request()->header('X-User-Roles', '');
                $rolesParts = [];
                if ($rolesHdr !== '') {
                    $rolesParts = preg_split('/[,\s]+/', $rolesHdr) ?: [];
                    $rolesParts = array_values(array_unique(array_map(function ($r) { return strtolower(trim((string) $r)); }, $rolesParts)));
                    if (in_array('admin', $rolesParts, true)) {
                        return true;
                    }
                }

                // If caller has department_admin role, allow as long as department_code is a valid canonical code.
                if (!empty($rolesParts) && in_array('department_admin', $rolesParts, true)) {
                    $wantTmp = strtolower(trim((string) $departmentCode));
                    $codes = config('departments.codes', ['registrar','finance','admissions','building_admin','purchasing','academics','clinic','guidance','osas']);
                    $codes = array_values(array_unique(array_map(function ($c) { return strtolower(trim((string) $c)); }, (array) $codes)));
                    if (in_array($wantTmp, $codes, true)) {
                        return true;
                    }
                }

                // Prefer acting faculty from middleware-injected request attribute,
                // then X-Faculty-ID header, then fallback to authenticated user
                $uid = null;

                // 1) Middleware (RequireRole) attaches 'faculty' to request attributes
                try {
                    $facAttr = request()->attributes->get('faculty');
                    if ($facAttr && isset($facAttr->intID)) {
                        $uid = (int) $facAttr->intID;
                    }
                } catch (\Throwable $e) { /* ignore */ }

                // 2) Header fallback
                if (!$uid) {
                    $hdr = request()->header('X-Faculty-ID');
                    if ($hdr !== null && $hdr !== '' && is_numeric($hdr)) {
                        $uid = (int) $hdr;
                    }
                }

                // 3) Authenticated user fallback
                if (!$uid && $user) {
                    $uid = (int) ($user->intID ?? 0);
                }

                if (!$uid || $uid <= 0) return false;

                $want = strtolower(trim((string) $departmentCode));
                $allowed = \App\Models\FacultyDepartment::allowedForFaculty($uid, null);
                return in_array($want, $allowed, true);
            } catch (\Throwable $e) {
                return false;
            }
        });

        Gate::define('department.deficiency.manage', function ($user, string $departmentCode) {
            try {
                // Admin bypass: authenticated user role OR X-User-Roles header contains 'admin'
                if ($user && self::userHasAnyRole($user, ['admin'])) return true;
                $rolesHdr = (string) request()->header('X-User-Roles', '');
                $rolesParts = [];
                if ($rolesHdr !== '') {
                    $rolesParts = preg_split('/[,\s]+/', $rolesHdr) ?: [];
                    $rolesParts = array_values(array_unique(array_map(function ($r) { return strtolower(trim((string) $r)); }, $rolesParts)));
                    if (in_array('admin', $rolesParts, true)) {
                        return true;
                    }
                }

                // If caller has department_admin role, allow as long as department_code is a valid canonical code.
                if (!empty($rolesParts) && in_array('department_admin', $rolesParts, true)) {
                    $wantTmp = strtolower(trim((string) $departmentCode));
                    $codes = config('departments.codes', ['registrar','finance','admissions','building_admin','purchasing','academics','clinic','guidance','osas']);
                    $codes = array_values(array_unique(array_map(function ($c) { return strtolower(trim((string) $c)); }, (array) $codes)));
                    if (in_array($wantTmp, $codes, true)) {
                        return true;
                    }
                }

                // Prefer acting faculty from middleware-injected request attribute,
                // then X-Faculty-ID header, then fallback to authenticated user
                $uid = null;

                // 1) Middleware (RequireRole) attaches 'faculty' to request attributes
                try {
                    $facAttr = request()->attributes->get('faculty');
                    if ($facAttr && isset($facAttr->intID)) {
                        $uid = (int) $facAttr->intID;
                    }
                } catch (\Throwable $e) { /* ignore */ }

                // 2) Header fallback
                if (!$uid) {
                    $hdr = request()->header('X-Faculty-ID');
                    if ($hdr !== null && $hdr !== '' && is_numeric($hdr)) {
                        $uid = (int) $hdr;
                    }
                }

                // 3) Authenticated user fallback
                if (!$uid && $user) {
                    $uid = (int) ($user->intID ?? 0);
                }

                if (!$uid || $uid <= 0) return false;

                $want = strtolower(trim((string) $departmentCode));
                $allowed = \App\Models\FacultyDepartment::allowedForFaculty($uid, null);
                return in_array($want, $allowed, true);
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
