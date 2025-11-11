<?php

namespace Bas\LaravelPayment;

use Bas\LaravelPayment\Services\PaymentService;
use Bas\LaravelPayment\Services\EncryptionService;
use Illuminate\Support\ServiceProvider;

class BasPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../config/bas-payment.php', 'bas-payment');

        // Register EncryptionService as singleton
        $this->app->singleton(EncryptionService::class, function ($app) {
            return new EncryptionService();
        });

        // Register PaymentService as singleton
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService($app->make(EncryptionService::class));
        });

        // Register facade accessor
        $this->app->bind('bas-payment', function ($app) {
            return $app->make(PaymentService::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bas-payment.php' => config_path('bas-payment.php'),
            ], 'bas-payment-config');
        }
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return ['bas-payment', PaymentService::class, EncryptionService::class];
    }
}
