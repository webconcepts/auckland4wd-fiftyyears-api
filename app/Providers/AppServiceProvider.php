<?php

namespace App\Providers;

use App\IdObfuscator;
use App\OptimusIdObfuscator;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OptimusIdObfuscator::class, function () {
            return new OptimusIdObfuscator(env('OPTIMUS_PRIME'), env('OPTIMUS_INVERSE'), env('OPTIMUS_RANDOM'));
        });

        $this->app->bind(IdObfuscator::class, OptimusIdObfuscator::class);
    }
}
