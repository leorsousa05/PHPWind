# Known Issues

## Duplicate import prevents doctor handler from loading

- **Symptom:** `php -l src/Command/DoctorHandler.php` fails with a duplicate `PlatformResolver` import fatal error.
- **Trigger:** `PHPWind\Binary\PlatformResolver` is imported twice in `DoctorHandler.php`.
- **Mitigation:** Remove the duplicate import, then run PHP lint and PHPUnit.
- **Status:** Fixed and verified; `php -l src/Command/DoctorHandler.php` passes; full PHPUnit passes (102 tests, 199 assertions, 1 skipped).
