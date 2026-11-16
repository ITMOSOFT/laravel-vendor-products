<?php

namespace ItmoSoft\VendorProducts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ItmoSoft\Vendors\Models\Vendor;

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

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product(): MorphTo
    {
        return $this->morphTo();
    }
}
