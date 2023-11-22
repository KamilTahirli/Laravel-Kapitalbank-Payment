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
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/payment.php' => config_path('payment.php'),
            __DIR__.'/Controllers/KapitalPaymentController.php' => app_path('Http/Controllers/KapitalPaymentController.php'),
        ]);
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
