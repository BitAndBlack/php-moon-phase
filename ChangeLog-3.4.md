# Changes in Solaris Moon Phase v3.4

## 3.4.0 2026-09-09

### Added

-   New `MoonPhaseName` enum that represents all phases of the Moon with a machine-readable `->value` and a human-readable `label()`.
-   New `getPhaseByEnum()` to look up the UNIX timestamp of a phase via the `MoonPhaseName` enum.
-   New `getPhaseNameEnum()` to get the current phase as a `MoonPhaseName` enum.
-   New `getPhaseByEnumDateTime()` and new `getPhaseNewMoonDateTime()`, `getPhaseFirstQuarterDateTime()`, `getPhaseFullMoonDateTime()`, `getPhaseLastQuarterDateTime()`, `getPhaseNextNewMoonDateTime()`, `getPhaseNextFirstQuarterDateTime()`, `getPhaseNextFullMoonDateTime()` and `getPhaseNextLastQuarterDateTime()` getters, which return the quarter times as `DateTimeImmutable` objects in UTC instead of UNIX timestamps.
-   New `Exception` class in the `Solaris` namespace that is thrown when no phase data is available.
-   Extensive test coverage, including tests for every method of the library.

### Changed

-   The quarter-time getters now use the `MoonPhaseName` enum internally.
-   All methods and properties are now documented with detailed docblocks.

### Deprecated

-   `getPhaseByName()` is deprecated in favor of `getPhaseByEnum()`. It will be removed in the next major version.
-   `getPhaseName()` is deprecated in favor of `getPhaseNameEnum()`. It will be removed in the next major version.