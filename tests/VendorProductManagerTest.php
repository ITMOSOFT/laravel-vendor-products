<?php

namespace ItmoSoft\VendorProducts\Tests;

use ItmoSoft\VendorProducts\Tests\Models\Product;
use ItmoSoft\VendorProducts\VendorProductManager;
use ItmoSoft\Vendors\Models\Vendor;

class VendorProductManagerTest extends TestCase
{
    public function test_attach_creates_assignment_and_persists_metadata(): void
    {
        $vendor = Vendor::create(['name' => 'Seller', 'slug' => 'seller']);
        $product = Product::create(['name' => 'Desk']);

        $assignment = app(VendorProductManager::class)->attach($vendor, $product, ['source' => 'import']);

        $this->assertSame($vendor->getKey(), $assignment->vendor_id);
        $this->assertSame($product->getKey(), $assignment->product->getKey());
        $this->assertSame(['source' => 'import'], $assignment->metadata);
    }

    public function test_attach_is_idempotent_for_same_vendor_and_product(): void
    {
        $vendor = Vendor::create(['name' => 'Seller', 'slug' => 'seller']);
        $product = Product::create(['name' => 'Desk']);
        $manager = app(VendorProductManager::class);

        $manager->attach($vendor, $product);
        $manager->attach($vendor, $product, ['updated' => true]);

        $this->assertSame(1, $manager->assignments($vendor)->count());
    }

    public function test_detach_removes_assignment(): void
    {
        $vendor = Vendor::create(['name' => 'Seller', 'slug' => 'seller']);
        $product = Product::create(['name' => 'Desk']);
        $manager = app(VendorProductManager::class);
        $manager->attach($vendor, $product);

        $this->assertSame(1, $manager->detach($vendor, $product));
        $this->assertSame(0, $manager->assignments($vendor)->count());
    }

    public function test_sync_replaces_vendor_product_assignments(): void
    {
        $vendor = Vendor::create(['name' => 'Seller', 'slug' => 'seller']);
        $first = Product::create(['name' => 'First']);
        $second = Product::create(['name' => 'Second']);
        $manager = app(VendorProductManager::class);

        $manager->attach($vendor, $first);
        $manager->sync($vendor, [$second]);

        $this->assertSame([$second->getKey()], $manager->assignments($vendor)->get()->pluck('product.id')->all());
    }

    public function test_vendors_for_product_finds_assignments(): void
    {
        $vendor = Vendor::create(['name' => 'Seller', 'slug' => 'seller']);
        $product = Product::create(['name' => 'Desk']);
        app(VendorProductManager::class)->attach($vendor, $product);

        $this->assertSame($vendor->getKey(), app(VendorProductManager::class)->vendorsFor($product)->first()->vendor->getKey());
    }
}
