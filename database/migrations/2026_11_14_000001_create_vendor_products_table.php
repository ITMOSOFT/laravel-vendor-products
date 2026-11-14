<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('itmosoft-vendor-products.table_prefix', 'itmosoft_');

        Schema::create($prefix.'vendor_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->morphs('product');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'product_type', 'product_id'], 'vendor_products_vendor_product_unique');
        });
    }

    public function down(): void
    {
        $prefix = config('itmosoft-vendor-products.table_prefix', 'itmosoft_');
        Schema::dropIfExists($prefix.'vendor_products');
    }
};
