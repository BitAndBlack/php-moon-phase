[![PHP from Packagist](https://img.shields.io/packagist/php-v/solaris/php-moon-phase)](http://www.php.net)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/cd2e7203326345d582f5b5a5fd2ffc91)](https://www.codacy.com/gh/Moskito89/php-moon-phase/dashboard)
[![Latest Stable Version](https://poser.pugx.org/solaris/php-moon-phase/v/stable)](https://packagist.org/packages/solaris/php-moon-phase)
[![Total Downloads](https://poser.pugx.org/solaris/php-moon-phase/downloads)](https://packagist.org/packages/solaris/php-moon-phase)
[![License](https://poser.pugx.org/solaris/php-moon-phase/license)](https://packagist.org/packages/solaris/php-moon-phase)

# A PHP class for calculating the phase of the Moon.

MoonPhase is a PHP class for calculating the phase of the Moon, and other related variables. It is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).

## Installation

This library is made for the use with [Composer](https://packagist.org/packages/solaris/php-moon-phase). Add it to your project by running `$ composer require solaris/php-moon-phase`.

## Usage

Create an instance of the `Solaris\MoonPhase` class, supplying a UNIX timestamp for when you want to determine the moon phase (if you don't then the current time will be used). You can then use the following class functions to access the properties of the object:

 - `phase()`: the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
 - `get('illumination')`: the illuminated fraction of the Moon (0 = New, 1 = Full).
 - `get('age')`: the age of the Moon, in days.
 - `get('distance')`: the distance of the Moon from the centre of the Earth (kilometres).
 - `get('diameter')`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
 - `get('sundistance')`: the distance to the Sun (kilometres).
 - `get('sundiameter')`: the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
 - `get_phase('new_moon')`: the time of the New Moon in the current lunar cycle, i.e., the start of the current cycle (UNIX timestamp).
 - `get_phase('next_new_moon')`: the time of the New Moon in the next lunar cycle, i.e., the start of the next cycle (UNIX timestamp).
 - `get_phase('full_moon')`: the time of the Full Moon in the current lunar cycle (UNIX timestamp).
 - `get_phase('next_full_moon')`: the time of the Full Moon in the next lunar cycle (UNIX timestamp).
 - `get_phase('first_quarter')`: the time of the first quarter in the current lunar cycle (UNIX timestamp).
 - `get_phase('next_first_quarter')`: the time of the first quarter in the next lunar cycle (UNIX timestamp).
 - `get_phase('last_quarter')`: the time of the last quarter in the current lunar cycle (UNIX timestamp).
 - `get_phase('next_last_quarter')`: the time of the last quarter in the next lunar cycle (UNIX timestamp).
 - `phase_name()`: the [phase name](http://aa.usno.navy.mil/faq/docs/moon_phases.php).

### Example

```php
<?php

// create an instance of the class, and use the current time
$moon = new Solaris\MoonPhase();

$age = round($moon->get('age'), 1);
$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
$distance = round($moon->get('distance'), 2);
$next = gmdate('G:i:s, j M Y', $moon->get_phase('next_new_moon'));

echo "The moon is currently $age days old, and is therefore $stage. ";
echo "It is $distance km from the centre of the Earth. ";
echo "The next new moon is at $next.";
```

## Help

If you have any questions, feel free to contact us under `hello@bitandblack.com`.

Further information about Bit&Black can be found under [www.bitandblack.com](https://www.bitandblack.com).