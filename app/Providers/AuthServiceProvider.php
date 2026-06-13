<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */

    protected $policies = [
        \App\Models\Session::class => \App\Policies\AttendancePolicy::class,
        \App\Models\ExcuseRequest::class => \App\Policies\ExcusePolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
