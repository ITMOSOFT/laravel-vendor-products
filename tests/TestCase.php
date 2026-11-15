<?php

namespace ItmoSoft\VendorProducts\Tests;

use ItmoSoft\VendorProducts\VendorProductsServiceProvider;
use ItmoSoft\Vendors\VendorsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            VendorsServiceProvider::class,
            VendorProductsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
