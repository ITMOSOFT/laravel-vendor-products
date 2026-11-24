<?php

namespace ItmoSoft\VendorProducts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use InvalidArgumentException;
use ItmoSoft\Vendors\Models\Vendor;

class VendorProduct extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        $prefix = config('itmosoft-vendor-products.table_prefix', 'itmosoft_');

        if (! is_string($prefix) || preg_match('/^[A-Za-z0-9_]*$/D', $prefix) !== 1) {
            throw new InvalidArgumentException(
                'The vendor product table_prefix must contain only letters, numbers and underscores.'
            );
        }

        return $prefix.'vendor_products';
    }

    public function vendor(): BelongsTo
    {
        $vendorModel = config('itmosoft-vendor-products.vendor_model', Vendor::class);

        if (
            ! is_string($vendorModel)
            || ! class_exists($vendorModel)
            || ! is_a($vendorModel, Model::class, true)
        ) {
            throw new InvalidArgumentException(
                'The vendor_model configuration must be an existing Eloquent model class.'
            );
        }

        return $this->belongsTo($vendorModel, 'vendor_id');
    }

    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
