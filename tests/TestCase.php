<?php

namespace ItmoSoft\VendorProducts\Tests;

use ItmoSoft\VendorProducts\VendorProductsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [VendorProductsServiceProvider::class];
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

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('vendors', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
        \Illuminate\Support\Facades\Schema::create('products', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->artisan('migrate')->run();
    }
}
