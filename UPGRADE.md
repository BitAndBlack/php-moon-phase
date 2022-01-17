# Upgrades

## 1.x to 2.0

The `v2.0` release does not add any new features and does not remove any. Instead, some changes have been made to bring the library up to current standards:

- The PSR-0 autoloader has been replaced by the PSR-4 autoloader.
- The directory structure has been adapted to PSR-4.
- Tests with PHPUnit have been added to ensure safe development.
- PHPStan was also added to perform static analysis.

If you include the MoonPhase class manually, please update the path.