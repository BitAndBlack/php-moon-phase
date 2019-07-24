# A PHP class for calculating the phase of the Moon.

MoonPhase is a PHP class for calculating the phase of the Moon, and other related variables. It is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).

## Installation

MoonPhase is available on Packagist ([solaris/php-moon-phase](http://packagist.org/packages/solaris/php-moon-phase))
and can be installed using [Composer](http://getcomposer.org/). Alternatively you can grab the code directly from GitHub and include the `MoonPhase.php` script directly or via a PSR-0 autoloader.

## Usage

Create an instance of the `Solaris\MoonPhase` class, supplying a UNIX timestamp for when you want to determine the moon phase (if you don't then the current time will be used). You can then use the following class functions to access the properties of the object:

 - `phase()`: the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
 - `get('illumination')`: the illuminated fraction of the Moon (0 = New, 1 = Full).
 - `get('age')`: the age of the Moon, in days.
 - `get('distance')`: the distance of the Moon from the centre of the Earth (kilometres).
 - `get('diameter')`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
 - `get('sundistance')`: the distance to the Sun (kilometres).
 - `get('sundiameter')`: the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
 - `get_phase('new_moon')`: the time of the last New Moon (UNIX timestamp).
 - `get_phase('next_new_moon')`: the time of the next New Moon (UNIX timestamp).
 - `get_phase('full_moon')`: the time of the Full Moon in the current lunar cycle (UNIX timestamp).
 - `get_phase('next_full_moon')`: the time of the next Full Moon in the current lunar cycle (UNIX timestamp).
 - `get_phase('first_quarter')`: the time of the first quarter in the current lunar cycle (UNIX timestamp).
 - `get_phase('next_first_quarter')`: the time of the next first quarter in the current lunar cycle (UNIX timestamp).
 - `get_phase('last_quarter')`: the time of the last quarter in the current lunar cycle (UNIX timestamp).
 - `get_phase('next_last_quarter')`: the time of the next last quarter in the current lunar cycle (UNIX timestamp).
 - `phase_name()`: the [phase name](http://aa.usno.navy.mil/faq/docs/moon_phases.php).

### Example

	// create an instance of the class, and use the current time
	$moon = new Solaris\MoonPhase();
	$age = round($moon->get('age'), 1);
	$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
	$distance = round($moon->get('distance'), 2);
	$next = gmdate('G:i:s, j M Y', $moon->get_phase('next_new_moon'));
	echo "The moon is currently $age days old, and is therefore $stage. ";
	echo "It is $distance km from the centre of the Earth. ";
	echo "The next new moon is at $next.";

## Help

For bugs/enhancements, feel free to either raise an issue or pull request in GitHub, or [contact me](http://rayofsolaris.net/contact/).
