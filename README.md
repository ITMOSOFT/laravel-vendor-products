# ITMOSOFT Laravel Vendor Products

Associate marketplace vendors with polymorphic product models. Compatible with Laravel 12 and 13.

## Features

- Attach persisted Eloquent product models to a vendor.
- Polymorphic product references.
- Detach product assignments.
- Replace a vendor's assignments with `sync()`.
- Query products assigned to a vendor.
- Find vendors associated with a product.
- JSON assignment metadata.
- Duplicate assignment prevention through a unique database index.
- Configurable table prefix and vendor model class.
- Input validation for persisted models and sync operations.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- `itmosoft/laravel-vendors` ^1.0

## Installation

```bash
composer require itmosoft/laravel-vendor-products
php artisan vendor:publish --tag=itmosoft-vendor-products-config
php artisan migrate
```

The default migration creates `itmosoft_vendor_products`. Configure `table_prefix` before migrating if needed.

## Vendor model configuration

The default vendor model is `ItmoSoft\\Vendors\\Models\\Vendor` from `itmosoft/laravel-vendors`.

If your application uses a different Eloquent vendor model, set `vendor_model` in the published `config/itmosoft-vendor-products.php` file:

```php
return [
    'table_prefix' => 'itmosoft_',
    'vendor_model' => App\Models\Vendor::class,
];
```

The configured class must exist and extend `Illuminate\\Database\\Eloquent\\Model`. It must use a compatible primary key that matches the unsigned big integer `vendor_id` column. The package does not create a foreign key to the vendor table because the vendor package table name may be customized.

## Attach a product

```php
use ItmoSoft\\VendorProducts\\VendorProductManager;

$manager = app(VendorProductManager::class);

$assignment = $manager->attach(
    vendor: $vendor,
    product: $product,
    metadata: ['source' => 'seller-dashboard'],
);
```

Both the vendor and product must be persisted Eloquent models. The vendor must be an instance of the configured `vendor_model`.

Attaching the same vendor/product pair again updates its metadata instead of creating another assignment. The database unique index remains the final protection against concurrent duplicate inserts; handle duplicate-key exceptions in the host application if concurrent requests may attach the same pair.

## List vendor products

```php
$assignments = $manager->assignments($vendor)->with('product')->paginate(20);
```

## Detach or sync

```php
$manager->detach($vendor, $product);

$manager->sync($vendor, [$productA, $productB]);
```

`sync()` replaces every assignment for the given vendor. All supplied products are validated before existing assignments are deleted. Repeated instances of the same product are deduplicated. Call `sync()` only after authorization and with the complete intended product list.

## Find vendors for a product

```php
$assignments = $manager->vendorsFor($product)->with('vendor')->get();
```

The `vendor` relationship resolves to the configured `vendor_model`.

## Important scope and production notes

- This package manages vendor-to-product assignments only. It does not create products, enforce product ownership at controller level, publish products, manage inventory/pricing, or provide storefront APIs.
- The package does not enforce that a vendor is approved before assigning products. Enforce that business rule in the host application.
- The product side is polymorphic; restrict allowed product model types in your application.
- `sync()` is destructive by design for the specified vendor. Protect it with application authorization and pass the complete intended list.
- The package does not add a foreign key to the vendor table. Ensure vendor records are managed consistently and consider database-level constraints if your deployment has a fixed vendor table.
- Add tenant scoping, authorization, audit logging and soft-delete behavior according to your marketplace requirements.

## Testing

```bash
composer install
composer validate --strict
composer test
```

## License

MIT. See [LICENSE](LICENSE).
