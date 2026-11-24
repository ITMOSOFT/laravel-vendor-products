<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function tableName(): string
    {
        $prefix = config('itmosoft-vendor-products.table_prefix', 'itmosoft_');

        if (! is_string($prefix) || preg_match('/^[A-Za-z0-9_]*$/D', $prefix) !== 1) {
            throw new \InvalidArgumentException(
                'The vendor product table_prefix must contain only letters, numbers and underscores.'
            );
        }

        return $prefix.'vendor_products';
    }

    public function up(): void
    {
        Schema::create($this->tableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->morphs('product');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(
                ['vendor_id', 'product_type', 'product_id'],
                'vendor_products_vendor_product_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName());
    }
};
