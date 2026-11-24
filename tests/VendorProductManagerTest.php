<?php

namespace ItmoSoft\VendorProducts\Tests;

use InvalidArgumentException;
use ItmoSoft\VendorProducts\Models\VendorProduct;
use ItmoSoft\VendorProducts\Tests\Models\Owner;
use ItmoSoft\VendorProducts\Tests\Models\Product;
use ItmoSoft\VendorProducts\Tests\Models\Vendor as TestVendor;
use ItmoSoft\VendorProducts\VendorProductManager;
use ItmoSoft\Vendors\Models\Vendor;

class VendorProductManagerTest extends TestCase
{
    private function createVendor(): Vendor
    {
        $owner = Owner::create(['name' => 'Seller Owner']);

        return Vendor::create([
            'owner_type' => Owner::class,
            'owner_id' => $owner->getKey(),
            'name' => 'Seller',
            'slug' => 'seller',
        ]);
    }

    public function test_attach_creates_assignment_and_persists_metadata(): void
    {
        $vendor = $this->createVendor();
        $product = Product::create(['name' => 'Desk']);

        $assignment = app(VendorProductManager::class)->attach(
            $vendor,
            $product,
            ['source' => 'import']
        );

        $this->assertSame($vendor->getKey(), $assignment->vendor_id);
        $this->assertSame($product->getKey(), $assignment->product->getKey());
        $this->assertSame(['source' => 'import'], $assignment->metadata);
        $this->assertSame($vendor->getKey(), $assignment->vendor->getKey());
    }

    public function test_attach_is_idempotent_and_updates_metadata(): void
    {
        $vendor = $this->createVendor();
        $product = Product::create(['name' => 'Desk']);
        $manager = app(VendorProductManager::class);

        $first = $manager->attach($vendor, $product, ['source' => 'initial']);
        $second = $manager->attach($vendor, $product, ['source' => 'updated']);

        $this->assertSame($first->getKey(), $second->getKey());
        $this->assertSame(['source' => 'updated'], $second->metadata);
        $this->assertSame(1, $manager->assignments($vendor)->count());
    }

    public function test_attach_rejects_unsaved_vendor_and_product(): void
    {
        $manager = app(VendorProductManager::class);
        $savedProduct = Product::create(['name' => 'Desk']);

        try {
            $manager->attach(new Vendor(), $savedProduct);
            $this->fail('An unsaved vendor should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('The vendor must be a persisted Eloquent model.', $exception->getMessage());
        }

        try {
            $manager->attach($this->createVendor(), new Product(['name' => 'Unsaved']));
            $this->fail('An unsaved product should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('The product must be a persisted Eloquent model.', $exception->getMessage());
        }
    }

    public function test_attach_rejects_wrong_vendor_model_class(): void
    {
        $owner = Owner::create(['name' => 'Owner']);
        $manager = app(VendorProductManager::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configured vendor model');

        $manager->attach($owner, Product::create(['name' => 'Desk']));
    }

    public function test_detach_removes_assignment(): void
    {
        $vendor = $this->createVendor();
        $product = Product::create(['name' => 'Desk']);
        $manager = app(VendorProductManager::class);

        $manager->attach($vendor, $product);

        $this->assertSame(1, $manager->detach($vendor, $product));
        $this->assertSame(0, $manager->assignments($vendor)->count());
    }

    public function test_sync_replaces_vendor_product_assignments(): void
    {
        $vendor = $this->createVendor();
        $first = Product::create(['name' => 'First']);
        $second = Product::create(['name' => 'Second']);
        $manager = app(VendorProductManager::class);

        $manager->attach($vendor, $first);
        $manager->sync($vendor, [$second]);

        $this->assertSame(
            [$second->getKey()],
            $manager->assignments($vendor)->get()->pluck('product.id')->all()
        );
    }

    public function test_sync_deduplicates_repeated_products(): void
    {
        $vendor = $this->createVendor();
        $product = Product::create(['name' => 'Repeated']);
        $manager = app(VendorProductManager::class);

        $manager->sync($vendor, [$product, $product]);

        $this->assertSame(1, $manager->assignments($vendor)->count());
    }

    public function test_sync_validates_all_products_before_replacing_assignments(): void
    {
        $vendor = $this->createVendor();
        $existing = Product::create(['name' => 'Existing']);
        $validNew = Product::create(['name' => 'New']);
        $unsaved = new Product(['name' => 'Unsaved']);
        $manager = app(VendorProductManager::class);

        $manager->attach($vendor, $existing);

        try {
            $manager->sync($vendor, [$validNew, $unsaved]);
            $this->fail('Sync should reject an unsaved product.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('The product must be a persisted Eloquent model.', $exception->getMessage());
        }

        $this->assertSame(
            [$existing->getKey()],
            $manager->assignments($vendor)->get()->pluck('product.id')->all()
        );
    }

    public function test_vendors_for_product_finds_assignments(): void
    {
        $vendor = $this->createVendor();
        $product = Product::create(['name' => 'Desk']);

        app(VendorProductManager::class)->attach($vendor, $product);

        $this->assertSame(
            $vendor->getKey(),
            app(VendorProductManager::class)
                ->vendorsFor($product)
                ->first()
                ->vendor
                ->getKey()
        );
    }

    public function test_custom_vendor_model_configuration_is_used_by_manager_and_relation(): void
    {
        config(['itmosoft-vendor-products.vendor_model' => TestVendor::class]);

        $owner = Owner::create(['name' => 'Custom Seller Owner']);
        $vendor = TestVendor::create([
            'owner_type' => Owner::class,
            'owner_id' => $owner->getKey(),
            'name' => 'Custom Seller',
            'slug' => 'custom-seller',
        ]);
        $product = Product::create(['name' => 'Custom Product']);
        $manager = new VendorProductManager([
            'vendor_model' => TestVendor::class,
            'table_prefix' => 'itmosoft_',
        ]);

        $assignment = $manager->attach($vendor, $product);

        $this->assertInstanceOf(TestVendor::class, $assignment->vendor);
        $this->assertSame($vendor->getKey(), $assignment->vendor->getKey());
    }

    public function test_manager_rejects_invalid_table_prefix_and_vendor_model_configuration(): void
    {
        foreach (['bad-prefix-', 'prefix.name', 'prefix name', 123] as $prefix) {
            try {
                new VendorProductManager(['table_prefix' => $prefix]);
                $this->fail('An invalid table prefix was accepted.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('table_prefix', $exception->getMessage());
            }
        }

        foreach (['Missing\\VendorModel', stdClass::class, 123] as $vendorModel) {
            try {
                new VendorProductManager(['vendor_model' => $vendorModel]);
                $this->fail('An invalid vendor model was accepted.');
            } catch (InvalidArgumentException $exception) {
                $this->assertStringContainsString('vendor_model', $exception->getMessage());
            }
        }
    }

    public function test_vendor_product_model_rejects_invalid_table_prefix(): void
    {
        config(['itmosoft-vendor-products.table_prefix' => 'invalid-prefix-']);

        $this->expectException(InvalidArgumentException::class);
        (new VendorProduct())->getTable();
    }
}
