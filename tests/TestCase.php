<?php

namespace ItmoSoft\VendorProducts\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(
            dirname(__DIR__, 2).'/../laravel-vendors/database/migrations'
        );

        $this->loadMigrationsFrom(
            dirname(__DIR__).'/database/migrations'
        );

        $this->artisan('migrate')->run();

        Schema::create('test_owners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
}
