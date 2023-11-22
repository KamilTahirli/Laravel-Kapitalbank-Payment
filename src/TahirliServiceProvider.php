<?php

namespace Tahirli\Kapitalbank;

use Illuminate\Support\ServiceProvider;

class TahirliServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/payment.php', 'payment');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/payment.php' => config_path('payment.php'),
            ], 'config');
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
