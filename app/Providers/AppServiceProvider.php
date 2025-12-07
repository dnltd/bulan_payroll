<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
{
    $this->app->singleton(\App\Services\PayrollService::class, function ($app) {
        return new \App\Services\PayrollService();
    });
}

    /**
     * Bootstrap any application services.
     */
    
}
