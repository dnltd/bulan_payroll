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
    public function boot()
    {
        Response::macro('xml', function ($data, $status = 200, $rootElement = 'response') {
            // data must be array
            if (!is_array($data)) {
                $data = (array) $data;
            }
            $xml = ArrayToXml::convert([$rootElement => $data], $rootElement);
            return response($xml, $status)->header('Content-Type', 'application/xml');
        });
    }
}
