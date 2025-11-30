<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services into the container
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService;
        });
        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService;
        });
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService;
        });
        $this->app->singleton(InvoiceService::class, function ($app) {
            return new InvoiceService;
        });
        $this->app->singleton(ShippingService::class, function ($app) {
            return new ShippingService;
        });

        // register checkout facade binding
        $this->app->singleton('checkout', function ($app) {
            return new CheckoutService(
                $app->make(CartService::class),
                $app->make(InventoryService::class),
                $app->make(PaymentService::class),
                $app->make(InvoiceService::class),
                $app->make(ShippingService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
