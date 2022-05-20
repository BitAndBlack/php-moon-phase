# Changes in Solaris Moon Phase v2.1

## 2.1.0 2022-05-20

### Changed 

- A lot of method names have been refactored to match the coding conventions of [PSR-1](https://www.php-fig.org/psr/psr-1/) and [PSR-12](https://www.php-fig.org/psr/psr-12/). For example `phase_name()` is now `getPhaseName()`. Methods with parameters have been split, for example `get_phase('new_moon')` is now `getPhaseNewMoon()`. Old methods are still available and can be used anyway. They will be removed in v3.0. To inform about those changes, deprecation messages have been added.