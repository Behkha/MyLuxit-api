<?php

namespace App\Providers;

use App\Models\Admin;
use App\Policies\AdminPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        Gate::policy(Admin::class, AdminPolicy::class);

        Gate::define('index-admins', function ($admin) {
            return in_array(Admin::PRIVILEGES['master'], $admin->privileges);
        });

        $this->app['auth']->viaRequest('api', function ($request) {
            return app('auth')->setRequest($request)->user();
        });
    }
}
