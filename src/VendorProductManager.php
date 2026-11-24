<?php

namespace ItmoSoft\VendorProducts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ItmoSoft\VendorProducts\Models\VendorProduct;
use ItmoSoft\Vendors\Models\Vendor;

class VendorProductManager
{
    protected string $vendorModel;

    public function __construct(protected array $config)
    {
        $prefix = $this->config['table_prefix'] ?? 'itmosoft_';

        if (! is_string($prefix) || preg_match('/^[A-Za-z0-9_]*$/D', $prefix) !== 1) {
            throw new InvalidArgumentException(
                'The vendor product table_prefix must contain only letters, numbers and underscores.'
            );
        }

        $vendorModel = $this->config['vendor_model'] ?? Vendor::class;

        if (
            ! is_string($vendorModel)
            || ! class_exists($vendorModel)
            || ! is_a($vendorModel, Model::class, true)
        ) {
            throw new InvalidArgumentException(
                'The vendor_model configuration must be an existing Eloquent model class.'
            );
        }

        $this->config['table_prefix'] = $prefix;
        $this->vendorModel = $vendorModel;
    }

    public function attach(Model $vendor, Model $product, array $metadata = []): VendorProduct
    {
        $this->assertVendor($vendor);
        $this->assertPersistedProduct($product);

        $assignment = VendorProduct::query()->firstOrNew([
            'vendor_id' => $vendor->getKey(),
            'product_type' => $product->getMorphClass(),
            'product_id' => $product->getKey(),
        ]);

        $assignment->metadata = $metadata;
        $assignment->save();

        return $assignment->refresh();
    }

    public function detach(Model $vendor, Model $product): int
    {
        $this->assertVendor($vendor);
        $this->assertPersistedProduct($product);

        return VendorProduct::query()
            ->where('vendor_id', $vendor->getKey())
            ->where('product_type', $product->getMorphClass())
            ->where('product_id', $product->getKey())
            ->delete();
    }

    /**
     * Replace the vendor's assignments with the supplied persisted products.
     *
     * All inputs are validated before existing assignments are deleted.
     *
     * @param array<int, Model> $products
     */
    public function sync(Model $vendor, array $products): void
    {
        $this->assertVendor($vendor);

        $uniqueProducts = [];

        foreach ($products as $product) {
            if (! ($product instanceof Model)) {
                throw new InvalidArgumentException('Every product must be an Eloquent model.');
            }

            $this->assertPersistedProduct($product);

            $key = $product->getMorphClass().':'.(string) $product->getKey();
            $uniqueProducts[$key] = $product;
        }

        DB::transaction(function () use ($vendor, $uniqueProducts): void {
            VendorProduct::query()
                ->where('vendor_id', $vendor->getKey())
                ->delete();

            foreach ($uniqueProducts as $product) {
                $this->attach($vendor, $product);
            }
        });
    }

    public function assignments(Model $vendor): Builder
    {
        $this->assertVendor($vendor);

        return VendorProduct::query()
            ->where('vendor_id', $vendor->getKey())
            ->with('product')
            ->latest();
    }

    public function vendorsFor(Model $product): Builder
    {
        $this->assertPersistedProduct($product);

        return VendorProduct::query()
            ->where('product_type', $product->getMorphClass())
            ->where('product_id', $product->getKey())
            ->with('vendor');
    }

    protected function assertVendor(Model $vendor): void
    {
        if (! is_a($vendor, $this->vendorModel)) {
            throw new InvalidArgumentException(
                "The vendor must be an instance of the configured vendor model [{$this->vendorModel}]."
            );
        }

        if (! $vendor->exists || $vendor->getKey() === null) {
            throw new InvalidArgumentException('The vendor must be a persisted Eloquent model.');
        }
    }

    protected function assertPersistedProduct(Model $product): void
    {
        if (! $product->exists || $product->getKey() === null) {
            throw new InvalidArgumentException('The product must be a persisted Eloquent model.');
        }
    }
}
