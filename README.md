# ITMOSOFT Laravel Vendor Products

Associate marketplace vendors with polymorphic product models. Compatible with Laravel 12 and 13.

## Features

- Attach any persisted Eloquent product model to a vendor.
- Polymorphic product references.
- Detach product assignments.
- Replace a vendor's assignments with `sync()`.
- Query products assigned to a vendor.
- Find vendors associated with a product.
- JSON assignment metadata.
- Duplicate assignment prevention through a unique database index.
- Configurable table prefix.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-vendor-products
php artisan vendor:publish --tag=itmosoft-vendor-products-config
php artisan migrate
```

The default migration creates `itmosoft_vendor_products`. Configure `table_prefix` before migrating if needed.

## Vendor model dependency

The default `VendorProduct` relationship expects the vendor model from `itmosoft/laravel-vendors`:

```php
ItmoSoft\Vendors\Models\Vendor
```

Install `itmosoft/laravel-vendors` in the host application. If your application uses a different Vendor model, set `vendor_model` in `config/itmosoft-vendor-products.php` to its class name.

## Attach a product

```php
use ItmoSoft\VendorProducts\VendorProductManager;

$manager = app(VendorProductManager::class);

$assignment = $manager->attach(
    vendor: $vendor,
    product: $product,
    metadata: ['source' => 'seller-dashboard'],
);
```

## List vendor products

```php
$assignments = $manager->assignments($vendor)->with('product')->paginate(20);
```

## Detach or sync

```php
$manager->detach($vendor, $product);

$manager->sync($vendor, [$productA, $productB]);
```

## Find vendors for a product

```php
$assignments = $manager->vendorsFor($product)->with('vendor')->get();
```

## Important scope and production notes

- This package manages vendor-to-product assignments only. It does not create products, enforce product ownership at controller level, publish products, manage inventory/pricing, or provide storefront APIs.
- It expects vendor IDs from the configured vendor model. The `vendor_id` column intentionally has no database foreign key because the vendor package's table prefix/model may be customized; enforce referential integrity in your deployment as appropriate.
- `sync()` replaces every assignment for the given vendor. Call it only after authorization and with the complete intended product list.
- The package does not enforce that a vendor is approved before assigning products. Enforce that business rule in the host application.
- The product side is polymorphic; restrict allowed product model types in your application.
- Database uniqueness prevents duplicate vendor/product pairs. Concurrent attach attempts may still need duplicate-key handling in the host application.
- Add tenant scoping, authorization, audit logging and soft-delete behavior according to your marketplace requirements.

## Testing

```bash
composer install
composer validate --strict
composer test
```

## License

MIT. See [LICENSE](LICENSE).
