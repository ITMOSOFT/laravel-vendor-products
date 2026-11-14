<?php

namespace ItmoSoft\VendorProducts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ItmoSoft\VendorProducts\Models\VendorProduct;

class VendorProductManager
{
    public function __construct(protected array $config)
    {
    }

    public function attach(
        Model $vendor,
        Model $product,
        array $metadata = []
    ): VendorProduct {
        if (! $vendor->exists || ! $product->exists) {
            throw new InvalidArgumentException('Both vendor and product must be persisted models.');
        }

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
        return VendorProduct::query()
            ->where('vendor_id', $vendor->getKey())
            ->where('product_type', $product->getMorphClass())
            ->where('product_id', $product->getKey())
            ->delete();
    }

    /**
     * Replace the vendor's assignments with the supplied persisted products.
     *
     * @param array<int, Model> $products
     */
    public function sync(Model $vendor, array $products): void
    {
        DB::transaction(function () use ($vendor, $products) {
            VendorProduct::query()->where('vendor_id', $vendor->getKey())->delete();

            foreach ($products as $product) {
                if (! $product instanceof Model || ! $product->exists) {
                    throw new InvalidArgumentException('Every product must be a persisted Eloquent model.');
                }

                $this->attach($vendor, $product);
            }
        });
    }

    public function assignments(Model $vendor)
    {
        return VendorProduct::query()
            ->where('vendor_id', $vendor->getKey())
            ->with('product')
            ->latest();
    }

    public function vendorsFor(Model $product)
    {
        return VendorProduct::query()
            ->where('product_type', $product->getMorphClass())
            ->where('product_id', $product->getKey())
            ->with('vendor');
    }
}
