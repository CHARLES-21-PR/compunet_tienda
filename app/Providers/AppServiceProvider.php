<?php

namespace App\Providers;

use App\Services\NiubizPaymentService;
use App\Services\InvoiceService;
use App\Services\ShippingService;
use App\Services\InventoryService;
use App\Facades\CheckoutFacade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
      $this->app->singleton('checkout', function ($app) {
        return new CheckoutFacade(
            new NiubizPaymentService(),
            new InvoiceService(),
            new ShippingService(),
            new InventoryService()
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
