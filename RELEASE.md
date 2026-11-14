# Release checklist

1. Install `itmosoft/laravel-vendors` in the host application or configure your custom vendor model.
2. Run `composer validate --strict`.
3. Run `composer test` on PHP 8.3 with Laravel 12 and Laravel 13 dependency sets.
4. Push `main` and confirm both GitHub Actions matrix jobs pass.
5. Create and push the annotated tag:
   ```bash
   git tag -a v1.0.0 -m "Initial release"
   git push origin v1.0.0
   ```
6. Create GitHub Release `v1.0.0 — Initial Release`.
7. Submit `https://github.com/ITMOSOFT/laravel-vendor-products` to Packagist as `itmosoft/laravel-vendor-products`.
8. Configure the Packagist GitHub webhook and verify the version is listed.
