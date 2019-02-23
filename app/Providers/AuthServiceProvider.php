<?php

namespace App\Providers;

use App\User;
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
        $this->app['auth']->viaRequest('api', function ($request) {
            return app('auth')->setRequest($request)->user();
        });

        // Gate::before(function ($user, $ability) {
        //     if ($user->isEditor()) {
        //         return true;
        //     }
        // });
        Gate::define('update', function ($user, $object) {
            return $user->id == $object->user_id;
        });
        Gate::define('destroy', function ($user, $object) {
            return $user->id == $object->user_id;
        });
    }
}
