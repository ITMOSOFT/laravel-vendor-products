<?php

namespace ItmoSoft\VendorProducts;

use Illuminate\Support\ServiceProvider;

class VendorProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/vendor-products.php', 'itmosoft-vendor-products');
        $this->app->singleton(VendorProductManager::class, fn ($app) => new VendorProductManager(
            $app['config']->get('itmosoft-vendor-products', [])
        ));
        $this->app->alias(VendorProductManager::class, 'itmosoft.vendor-products');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/vendor-products.php' => config_path('itmosoft-vendor-products.php'),
            ], 'itmosoft-vendor-products-config');
        }
    }
}
