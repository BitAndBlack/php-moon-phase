# php-moon-phase - A PHP class for calculating the phase of the Moon, and other related variables.

php-dmarc is a small PHP class for calculating the phase of the Moon. It is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).

## Usage

Include the moon-phase.php file in your script, and then simply create an instance of the moonphase class, supplying a UNIX timestamp for when you want to determine the moon phase. The following variables will be created in the resulting object, which you can then access from your script:

 - `phase`: the terminator phase angle as a fraction of a full circle (i.e., 0 to 1)
 - `illum`: the illuminated fraction of the Moon (0 = New, 1 = Full)
 - `age`: the age of the Moon, in days
 - `dist`: the distance of the Moon from the centre of the Earth
 - `angdia`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth
 - `sundist`: the distance to the Sun in kilometres
 - `sunangdia`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth 

## Demo

Here's a [simple demonstration](http://rayofsolaris.net/code/moon-phase#demo) of what the class can calculate, along with a coding example.