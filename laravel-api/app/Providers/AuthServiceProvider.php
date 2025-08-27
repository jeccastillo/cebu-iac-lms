<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
    }
}
