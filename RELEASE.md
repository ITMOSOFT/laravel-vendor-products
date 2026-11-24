# Release checklist

1. Confirm `itmosoft/laravel-vendors` ^1.0 is available to Composer.
2. Run `composer validate --strict`.
3. Run `composer test` on PHP 8.3 with Laravel 12 and Laravel 13 dependency sets.
4. Push `main` and confirm both GitHub Actions matrix jobs pass.
5. Review the changes and commit them locally:
   ```bash
   git status
   git add src/VendorProductManager.php src/Models/VendorProduct.php database/migrations/2026_11_14_000001_create_vendor_products_table.php tests/TestCase.php tests/VendorProductManagerTest.php config/vendor-products.php README.md CHANGELOG.md RELEASE.md
   git commit -m "fix: harden vendor product assignments"
   git push origin main
   ```
6. Create and push the annotated tag:
   ```bash
   git tag -a v1.0.2 -m "Release v1.0.2"
   git push origin v1.0.2
   ```
7. Create GitHub Release `v1.0.2 — Vendor Model and Assignment Validation`.
8. Verify the new version appears on Packagist.
