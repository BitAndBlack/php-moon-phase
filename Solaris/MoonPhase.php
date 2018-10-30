<?php
/**
 * Moon phase calculation class
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw/)
 * by Samir Shah (http://rayofsolaris.net)
 * License: MIT
 *
 * Refactor by Jan Barasek <jan@barasek.com> http://baraja.cz
 */

namespace Solaris;

class MoonPhase
{

	/**
	 * @var int|null
	 */
	private $timestamp;

	/**
	 * @var float
	 */
	private $phase;

	/**
	 * @var float
	 */
	private $illum;

	/**
	 * @var float
	 */
	private $age;

	/**
	 * @var float
	 */
	private $distance;

	/**
	 * @var float
	 */
	private $angularDiameter;

	/**
	 * @var float
	 */
	private $sunDistance;

	/**
	 * @var float
	 */
	private $sunAngularDiameter;

	/**
	 * @var float
	 */
	private $synMonth;

	/**
	 * @var float[]
	 */
	private $quarters = [];

	/**
	 * @param \DateTime|int|string|null $date
	 */
	public function __construct($date = null)
	{
		if ($date === null) {
			$time = time();
		} elseif ($date instanceof \DateTime) {
			$time = $date->getTimestamp();
		} else {
			$time = is_numeric($date) ? $date : @strtotime($date);
		}

		$epoch = 2444238.5;           // Astronomical constant: 1980 January 0.0

		// ---- Constants defining the Sun's apparent orbit  */
		$elonge = 278.833540;         // Ecliptic longitude of the Sun at epoch 1980.0
		$elongp = 282.596403;         // Ecliptic longitude of the Sun at perigee
		$eccent = 0.016718;           // Eccentricity of Earth's orbit
		$sunMaxDistance = 1.495985e8; // Semi-major axis of Earth's orbit, km
		$sunAngularSize = 0.533128;   // Sun's angular size, degrees, at semi-major axis distance

		// ---- Elements of the Moon's orbit, epoch 1980.0
		$mmlong = 64.975464;          // Moon's mean longitude at the epoch
		$mmlongp = 349.383063;        // Mean longitude of the perigee at the epoch
		$mecc = 0.054900;             // Eccentricity of the Moon's orbit
		$moonAngularSize = 0.5181;    // Moon's angular size at distance a from Earth
		$msmax = 384401;              // Semi-major axis of Moon's orbit in km
		$synmonth = 29.53058868;      // Synodic month (new Moon to new Moon)
		$this->synMonth = $synmonth;
		$this->timestamp = $time;

		// date is coming in as a UNIX timstamp, so convert it to Julian
		$time = ($time / 86400) + 2440587.5;

		// ---- Calculation of the Sun's position
		$Day = $time - $epoch;                                // Date within epoch
		$N = $this->fixAngle((360 / 365.2422) * $Day);        // Mean anomaly of the Sun
		$M = $this->fixAngle($N + $elonge - $elongp);         // Convert from perigee co-ordinates to epoch 1980.0
		$Ec = $this->kepler($M, $eccent);                     // Solve equation of Kepler
		$Ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($Ec / 2);
		$Ec = 2 * rad2deg(atan($Ec));                         // True anomaly
		$lambdaSun = $this->fixAngle($Ec + $elongp);          // Sun's geocentric ecliptic longitude

		$F = ((1 + $eccent * cos(deg2rad($Ec))) / (1 - $eccent * $eccent)); // Orbital distance factor
		$sunDistance = $sunMaxDistance / $F;                                // Distance to Sun in km
		$sunAngular = $F * $sunAngularSize;                                 // Sun's angular size in degrees

		// ---- Calculation of the Moon's position
		$ml = $this->fixAngle(13.1763966 * $Day + $mmlong);                 // Moon's mean longitude
		$MM = $this->fixAngle($ml - 0.1114041 * $Day - $mmlongp);           // Moon's mean anomaly
		$Ev = 1.2739 * sin(deg2rad(2 * ($ml - $lambdaSun) - $MM));          // Evection
		$Ae = 0.1858 * sin(deg2rad($M));                                    // Annual equation
		$A3 = 0.37 * sin(deg2rad($M));                                      // Correction term
		$MmP = $MM + $Ev - $Ae - $A3;                                       // Corrected anomaly
		$mEc = 6.2886 * sin(deg2rad($MmP));                                 // Correction for the equation of the centre
		$A4 = 0.214 * sin(deg2rad(2 * $MmP));                               // Another correction term
		$lP = $ml + $Ev + $mEc - $Ae + $A4;                                 // Corrected longitude
		$V = 0.6583 * sin(deg2rad(2 * ($lP - $lambdaSun)));                 // Variation
		$lPP = $lP + $V;                                                    // True longitude

		// ---- Calculation of the phase of the Moon
		$MoonAge = $lPP - $lambdaSun;                                       // Age of the Moon in degrees
		$moonPhase = (1 - cos(deg2rad($MoonAge))) / 2;                      // Phase of the Moon

		// ---- Distance of moon from the centre of the Earth
		$moonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($MmP + $mEc)));
		$MoonDFrac = $moonDist / $msmax;
		$MoonAng = $moonAngularSize / $MoonDFrac;                           // Moon's angular diameter

		// ---- store results
		$this->phase = $this->fixAngle($MoonAge) / 360;                     // Phase (0 to 1)
		$this->illum = (float) $moonPhase;                                  // Illuminated fraction (0 to 1)
		$this->age = $synmonth * $this->phase;                              // Age of moon (days)
		$this->distance = (float) $moonDist;                                // Distance (kilometres)
		$this->angularDiameter = $MoonAng;                                           // Angular diameter (degrees)
		$this->sunDistance = $sunDistance;                                  // Distance to Sun (kilometres)
		$this->sunAngularDiameter = (float) $sunAngular;                    // Sun's angular diameter (degrees)
	}

	/**
	 * @return float (0 - 1)
	 */
	public function getPhaseRatio(): float
	{
		return $this->phase;
	}

	/**
	 * KEPLER: Solve the equation of Kepler.
	 *
	 * @return float
	 */
	public function illumination(): float
	{
		return $this->illum;
	}

	/**
	 * Calculates  time  of  the mean new Moon for a given
	 * base date.  This argument K to this function is the
	 * precomputed synodic month index, given by:
	 *
	 *    K = (year - 1900) * 12.3685
	 *
	 * where year is expressed as a year and fractional year.
	 *
	 * @return float
	 */
	public function getAge(): float
	{
		return $this->age;
	}

	/**
	 * Given a K value used to determine the mean phase of
	 * the new moon, and a phase selector
	 * (0.0, 0.25, 0.5, 0.75), obtain the true, corrected phase time.
	 *
	 * @return float
	 */
	public function getDistance(): float
	{
		return $this->distance;
	}

	/**
	 * Find time of phases of the moon which surround the current date.
	 * Five phases are found, starting and ending with the new moons which bound the  current lunation.
	 *
	 * @return float
	 */
	public function getDiameter(): float
	{
		return $this->angularDiameter;
	}

	/**
	 * Distance to Sun (kilometres)
	 *
	 * @return float
	 */
	public function getSunDistance(): float
	{
		return $this->sunDistance;
	}

	/**
	 * Sun's angular diameter (degrees)
	 *
	 * @return float
	 */
	public function getSunDiameter(): float
	{
		return $this->sunAngularDiameter;
	}

	/**
	 * @return float|null
	 */
	public function getNewMoon(): ?float
	{
		return $this->getPhase(0);
	}

	/**
	 * @return float|null
	 */
	public function getFirstQuarter(): ?float
	{
		return $this->getPhase(1);
	}

	/**
	 * @return float|null
	 */
	public function getFullMoon(): ?float
	{
		return $this->getPhase(2);
	}

	/**
	 * @return float|null
	 */
	public function getLastQuarter(): ?float
	{
		return $this->getPhase(3);
	}

	/**
	 * @return float|null
	 */
	public function getNextNewMoon(): ?float
	{
		return $this->getPhase(4);
	}

	/**
	 * @return float|null
	 */
	public function getNextFirstQuarter(): ?float
	{
		return $this->getPhase(5);
	}

	/**
	 * @return float|null
	 */
	public function getNextFullMoon(): ?float
	{
		return $this->getPhase(6);
	}

	/**
	 * @return float|null
	 */
	public function getNextLastQuarter(): ?float
	{
		return $this->getPhase(7);
	}

	/**
	 * @return string
	 */
	public function phaseName(): string
	{
		$names = [
			'New Moon',
			'Waxing Crescent',
			'First Quarter',
			'Waxing Gibbous',
			'Full Moon',
			'Waning Gibbous',
			'Third Quarter',
			'Waning Crescent',
			'New Moon',
		];

		return $names[(int) floor(($this->phase + 0.0625) * 8)];
	}

	/**
	 * @param int $n
	 * @return float|null
	 */
	public function getPhase(int $n): ?float
	{
		if ($this->quarters === []) {
			$this->phaseHunt();
		}

		return $this->quarters[$n] ?? null;
	}

	/**
	 * @param float $a
	 * @return float
	 */
	private function fixAngle(float $a): float
	{
		return ($a - 360 * floor($a / 360));
	}

	/**
	 * @param float $m
	 * @param float $ecc
	 * @param float $epsilon
	 * @return float
	 */
	private function kepler(float $m, float $ecc, float $epsilon = 1e-6): float
	{
		$e = $m = deg2rad($m);

		do {
			$delta = $e - $ecc * sin($e) - $m;
			$e -= $delta / (1 - $ecc * cos($e));
		} while (abs($delta) > $epsilon);

		return $e;
	}

	/**
	 * @param int $date
	 * @param float $k
	 * @return float
	 */
	private function meanPhase(int $date, float $k): float
	{
		$timeInJulian = ($date - 2415020.0) / 36525;
		$t2 = $timeInJulian * $timeInJulian;
		$t3 = $t2 * $timeInJulian;

		$nt1 = 2415020.75933 + $this->synMonth * $k
			+ 0.0001178 * $t2
			- 0.000000155 * $t3
			+ 0.00033 * sin(deg2rad(166.56 + 132.87 * $timeInJulian - 0.009173 * $t2));

		return $nt1;
	}

	/**
	 * @param float $k
	 * @param float $phase
	 * @return float|null
	 */
	private function truePhase(float $k, float $phase): ?float
	{
		$apcor = false;

		$k += $phase;                 // Add phase to new moon time
		$t = $k / 1236.85;            // Time in Julian centuries from 1900 January 0.5
		$t2 = $t * $t;                // Square for frequent use
		$t3 = $t2 * $t;               // Cube for frequent use
		$pt = 2415020.75933           // Mean time of phase
			+ $this->synMonth * $k
			+ 0.0001178 * $t2
			- 0.000000155 * $t3
			+ 0.00033 * sin(deg2rad(166.56 + 132.87 * $t - 0.009173 * $t2));

		$m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;          // Sun's mean anomaly
		$mPrime = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;    // Moon's mean anomaly
		$f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;          // Moon's argument of latitude
		if ($phase < 0.01 || abs($phase - 0.5) < 0.01) {
			// Corrections for New and Full Moon
			$pt += (0.1734 - 0.000393 * $t) * sin(deg2rad($m))
				+ 0.0021 * sin(deg2rad(2 * $m))
				- 0.4068 * sin(deg2rad($mPrime))
				+ 0.0161 * sin(deg2rad(2 * $mPrime))
				- 0.0004 * sin(deg2rad(3 * $mPrime))
				+ 0.0104 * sin(deg2rad(2 * $f))
				- 0.0051 * sin(deg2rad($m + $mPrime))
				- 0.0074 * sin(deg2rad($m - $mPrime))
				+ 0.0004 * sin(deg2rad(2 * $f + $m))
				- 0.0004 * sin(deg2rad(2 * $f - $m))
				- 0.0006 * sin(deg2rad(2 * $f + $mPrime))
				+ 0.0010 * sin(deg2rad(2 * $f - $mPrime))
				+ 0.0005 * sin(deg2rad($m + 2 * $mPrime));
			$apcor = true;
		} else if (abs($phase - 0.25) < 0.01 || abs($phase - 0.75) < 0.01) {
			$pt += (0.1721 - 0.0004 * $t) * sin(deg2rad($m))
				+ 0.0021 * sin(deg2rad(2 * $m))
				- 0.6280 * sin(deg2rad($mPrime))
				+ 0.0089 * sin(deg2rad(2 * $mPrime))
				- 0.0004 * sin(deg2rad(3 * $mPrime))
				+ 0.0079 * sin(deg2rad(2 * $f))
				- 0.0119 * sin(deg2rad($m + $mPrime))
				- 0.0047 * sin(deg2rad($m - $mPrime))
				+ 0.0003 * sin(deg2rad(2 * $f + $m))
				- 0.0004 * sin(deg2rad(2 * $f - $m))
				- 0.0006 * sin(deg2rad(2 * $f + $mPrime))
				+ 0.0021 * sin(deg2rad(2 * $f - $mPrime))
				+ 0.0003 * sin(deg2rad($m + 2 * $mPrime))
				+ 0.0004 * sin(deg2rad($m - 2 * $mPrime))
				- 0.0003 * sin(deg2rad(2 * $m + $mPrime));

			if ($phase < 0.5) { // First quarter correction
				$pt += 0.0028 - 0.0004 * cos(deg2rad($m)) + 0.0003 * cos(deg2rad($mPrime));
			} else { // Last quarter correction
				$pt += -0.0028 + 0.0004 * cos(deg2rad($m)) - 0.0003 * cos(deg2rad($mPrime));
			}

			$apcor = true;
		}

		return $apcor === true ? $pt : null;
	}

	private function phaseHunt(): void
	{
		$date = $this->utcToJulian($this->timestamp);
		$_date = $date - 45;
		$ats = $this->timestamp - 86400 * 45;
		$yy = (int) gmdate('Y', $ats);
		$mm = (int) gmdate('n', $ats);

		$k1 = floor(($yy + (($mm - 1) * (1 / 12)) - 1900) * 12.3685);
		$k2 = 0;
		$_date = $nt1 = $this->meanPhase($_date, $k1);

		while (true) {
			$_date += $this->synMonth;
			$k2 = $k1 + 1;
			$nt2 = $this->meanPhase($_date, $k2);

			// if nt2 is close to $date, then mean phase isn't good enough, we have to be more accurate
			if (abs($nt2 - $date) < 0.75) {
				$nt2 = $this->truePhase($k2, 0.0);
			}

			if ($nt1 <= $date && $nt2 > $date) {
				break;
			}

			$nt1 = $nt2;
			$k1 = $k2;
		}

		$julianResult = [
			$this->truePhase($k1, 0.0),
			$this->truePhase($k1, 0.25),
			$this->truePhase($k1, 0.5),
			$this->truePhase($k1, 0.75),
			$this->truePhase($k2, 0.0),
			$this->truePhase($k2, 0.25),
			$this->truePhase($k2, 0.5),
			$this->truePhase($k2, 0.75),
		];

		foreach ($julianResult as $julianDate) {
			$this->quarters[] = ($julianDate - 2440587.5) * 86400;
		}
	}

	/**
	 * @param int $timestamp
	 * @return float
	 */
	private function utcToJulian(int $timestamp): float
	{
		return $timestamp / 86400 + 2440587.5;
	}

}