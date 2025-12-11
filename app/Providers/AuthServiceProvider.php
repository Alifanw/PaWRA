<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\\Models\\Model' => 'App\\Policies\\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define authorization gates for common checks
        Gate::define('is_superadmin', function ($user) {
            return $user->hasRole('superadmin');
        });

        Gate::define('has_permission', function ($user, $permission) {
            return $user->hasPermission($permission);
        });

        Gate::define('manage_users', function ($user) {
            return $user->hasPermission('manage_users') || $user->hasRole('superadmin');
        });

        Gate::define('manage_employees', function ($user) {
            return $user->hasPermission('manage_employees') || $user->hasRole('superadmin');
        });

        Gate::define('view_attendance', function ($user) {
            return $user->hasPermission('view_attendance') || $user->hasRole('superadmin');
        });

        Gate::define('manage_bookings', function ($user) {
            return $user->hasPermission('manage_bookings') || $user->hasRole('superadmin');
        });
    }

    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
