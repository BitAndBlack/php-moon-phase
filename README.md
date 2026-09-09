[![PHP from Packagist](https://img.shields.io/packagist/php-v/solaris/php-moon-phase)](http://www.php.net)
[![Latest Stable Version](https://poser.pugx.org/solaris/php-moon-phase/v/stable)](https://packagist.org/packages/solaris/php-moon-phase)
[![Total Downloads](https://poser.pugx.org/solaris/php-moon-phase/downloads)](https://packagist.org/packages/solaris/php-moon-phase)
[![License](https://poser.pugx.org/solaris/php-moon-phase/license)](https://packagist.org/packages/solaris/php-moon-phase)

# Solaris PHP Moon Phase

Calculate the phases of the Moon in PHP. This library is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).

## Installation

This library is made for the use with [Composer](https://packagist.org/packages/solaris/php-moon-phase). Add it to your project by running `$ composer require solaris/php-moon-phase`.

## Usage

Create an instance of the `MoonPhase` class, supplying a `DateTime` object with a UNIX timestamp for when you want to determine the moon phase (if you don't then the current time will be used). 

You can then use the following methods:

-   `getPhase()`: the terminator phase angle as a fraction of a full circle (i.e., `0` to `1`). Both `0` and `1` correspond to a New Moon, and `0.5` corresponds to a Full Moon.
-   `getIllumination()`: the illuminated fraction of the Moon (`0` = New, `1` = Full).
-   `getAge()`: the age of the Moon, in days.
-   `getDistance()`: the distance of the Moon from the centre of the Earth (kilometres).
-   `getDiameter()`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
-   `getSunDistance()`: the distance to the Sun (kilometres).
-   `getSunDiameter()`: the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
-   `getPhaseNewMoon()`: the time of the New Moon in the current lunar cycle, i.e., the start of the current cycle (UNIX timestamp).
-   `getPhaseNextNewMoon()`: the time of the New Moon in the next lunar cycle, i.e., the start of the next cycle (UNIX timestamp).
-   `getPhaseFullMoon()`: the time of the Full Moon in the current lunar cycle (UNIX timestamp).
-   `getPhaseNextFullMoon()`: the time of the Full Moon in the next lunar cycle (UNIX timestamp).
-   `getPhaseFirstQuarter()`: the time of the first quarter in the current lunar cycle (UNIX timestamp).
-   `getPhaseNextFirstQuarter()`: the time of the first quarter in the next lunar cycle (UNIX timestamp).
-   `getPhaseLastQuarter()`: the time of the last quarter in the current lunar cycle (UNIX timestamp).
-   `getPhaseNextLastQuarter()`: the time of the last quarter in the next lunar cycle (UNIX timestamp).
-   `getPhaseByName(string $name)`: _deprecated, use `getPhaseByEnum(MoonPhaseName)` instead_. Returns the time of the given phase as a UNIX timestamp.
-   `getPhaseByEnum(MoonPhaseName $phase)`: the time of the given phase as a UNIX timestamp.
-   `getPhaseNameEnum()`: the current [phase name](https://aa.usno.navy.mil/faq/moon_phases) as a `MoonPhaseName` enum.
-   `getPhaseName()`: _deprecated, use `getPhaseNameEnum()` instead_. The current [phase name](https://aa.usno.navy.mil/faq/moon_phases) as a string.

Each of the quarter-time getters has an equivalent that returns a `DateTimeImmutable` object (in UTC) instead of a UNIX timestamp:

-   `getPhaseNewMoonDateTime()`, `getPhaseFirstQuarterDateTime()`, `getPhaseFullMoonDateTime()`, `getPhaseLastQuarterDateTime()`
-   `getPhaseNextNewMoonDateTime()`, `getPhaseNextFirstQuarterDateTime()`, `getPhaseNextFullMoonDateTime()`, `getPhaseNextLastQuarterDateTime()`
-   `getPhaseByEnumDateTime(MoonPhaseName $phase)`: the time of the given phase as a `DateTimeImmutable` object.

### The MoonPhaseName enum

The `MoonPhaseName` enum represents the individual phases of the Moon and is used by the enum-based methods above:

-   `MoonPhaseName::NEW_MOON`
-   `MoonPhaseName::WAXING_CRESCENT`
-   `MoonPhaseName::FIRST_QUARTER`
-   `MoonPhaseName::WAXING_GIBBOUS`
-   `MoonPhaseName::FULL_MOON`
-   `MoonPhaseName::WANING_GIBBOUS`
-   `MoonPhaseName::THIRD_QUARTER`
-   `MoonPhaseName::WANING_CRESCENT`

In addition to the eight lunar phases, it contains the next occurrences of the four main phases:

-   `MoonPhaseName::NEXT_NEW_MOON`
-   `MoonPhaseName::NEXT_FIRST_QUARTER`
-   `MoonPhaseName::NEXT_FULL_MOON`
-   `MoonPhaseName::NEXT_LAST_QUARTER`

Each case has a `->value` (a machine-readable identifier, e.g., `'new_moon'`) and a `label()` method returning the human-readable name (e.g., `'New Moon'`).

### Example

```php
<?php

use Solaris\MoonPhase;

$moonPhase = new MoonPhase();

$age = round($moonPhase->getAge(), 1);
$stage = $moonPhase->getPhase() < 0.5 ? 'waxing' : 'waning';
$distance = round($moonPhase->getDistance(), 2);
$next = $moonPhase->getPhaseNextNewMoonDateTime()->format('G:i:s, j M Y');

echo 'The moon is currently ' . $age . ' days old, and is therefore ' . $stage . '. ';
echo 'It is ' . $distance . ' km from the centre of the Earth. ';
echo 'The next new moon is at ' . $next . '. ';
```

## Help

If you have any questions, feel free to contact us under `hello@bitandblack.com`.

Further information about Bit&Black can be found under [www.bitandblack.com](https://www.bitandblack.com).
