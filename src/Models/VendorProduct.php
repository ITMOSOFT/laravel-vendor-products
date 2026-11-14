<?php

namespace ItmoSoft\VendorProducts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VendorProduct extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return config('itmosoft-vendor-products.table_prefix', 'itmosoft_').'vendor_products';
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(config('itmosoft-vendor-products.vendor_model', \ItmoSoft\Vendors\Models\Vendor::class), 'vendor_id');
    }

    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
