# Changelog

## [1.0.2] - 2026-11-24

### Fixed
- Validate vendor-product table-prefix configuration before using it in model and migration table names.
- Honor the documented `vendor_model` configuration for manager validation and the assignment's vendor relationship.
- Reject invalid vendor model configuration and non-persisted vendor/product models.
- Validate all `sync()` inputs before deleting existing assignments.
- Deduplicate repeated product models passed to `sync()`.
- Remove environment-specific migration paths from the integration test bootstrap.

### Tests
- Expand coverage for custom vendor models, invalid configuration, unsaved models, sync safety, and duplicate products.

## [1.0.1] - 2026-11-16

- Corrected vendor model integration in the test suite.
- Added polymorphic vendor owner setup.
- Fixed missing vendor and product test tables.
- Ensured package migrations are loaded during integration tests.
- Added `itmosoft/laravel-vendors` as a required dependency.

## [1.0.0] - 2026-11-14

- Initial release.
- Added polymorphic vendor-to-product assignments.
- Added attach, detach and sync operations.
- Added vendor product and product vendor queries.
- Added JSON metadata and duplicate assignment protection.
- Added Laravel 12/13 compatibility workflow and PHPUnit tests.
