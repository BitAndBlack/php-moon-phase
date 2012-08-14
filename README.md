# A PHP class for calculating the phase of the Moon.

php-moon-phase is a PHP class for calculating the phase of the Moon, and other related variables. It is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).

## Usage

Include the moon-phase.php file in your script, and then simply create an instance of the `MoonPhase` class, supplying a UNIX timestamp for when you want to determine the moon phase. You can then use the following class functions to access the properties of the object:

 - `phase()`: the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
 - `illumination()`: the illuminated fraction of the Moon (0 = New, 1 = Full).
 - `age()`: the age of the Moon, in days.
 - `distance()`: the distance of the Moon from the centre of the Earth (kilometres).
 - `diameter()`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (radians).
 - `sundistance()`: the distance to the Sun (kilometres).
 - `sundiameter()`: the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (radians).
 - `new_moon()`: the time of the last New Moon (UNIX timestamp).
 - `next_new_moon()`: the time of the next New Moon (UNIX timestamp).
 - `full_moon()`: the time of the Full Moon in the current lunar cycle (UNIX timestamp).
 - `first_quarter()`: the time of the first quarter in the current lunar cycle (UNIX timestamp).
 - `last_quarter()`: the time of the last quarter in the current lunar cycle (UNIX timestamp).

### Example

	include 'moon-phase.php';

	// create an instance of the class, and use the current time
	$moon = new MoonPhase( time() );
	$age = round( $moon->age(), 1 );
	$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
	$distance = round( $moon->distance(), 2 );
	$next = gmdate( 'G:i:s, j M Y', $moon->next_new_moon() );
	echo "The moon is currently $age days old, and is therefore $stage. ";
	echo "It is $distance km from the centre of the Earth. ";
	echo "The next new moon is at $next.";

## Help

For bugs/enhancements, feel free to either raise an issue or pull request in GitHub, or [contact me](http://rayofsolaris.net/contact/).