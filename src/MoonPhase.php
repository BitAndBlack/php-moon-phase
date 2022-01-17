<?php
/**
*
* Moon phase calculation class
* Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw/)
* by Samir Shah (http://rayofsolaris.net)
* License: MIT
*
*/
namespace Solaris;

/**
* MoonPhase class
*/
class MoonPhase
{
	/** @var int timestamp */
	protected $timestamp;

	/** @var float phase */
	protected $phase;

	/** @var float illumination */
	protected $illumination;

	/** @var float age */
	protected $age;

	/** @var float distance */
	protected $distance;

	/** @var float diameter */
	protected $diameter;

	/** @var float sundistance */
	protected $sundistance;

	/** @var float sundiameter */
	protected $sundiameter;

	/** @var float synmonth */
	protected $synmonth;

	/** @var array quarters */
	protected $quarters = false;

	/** @var array degrees */
	protected $age_in_degrees;

	/**
     * Constructor
     *
     * @param \DateTime|null $date
     */
	{
		if (is_null($date)) {
			$date = time();
		}
		elseif ($date instanceof \DateTime) {
			$date = $date->getTimestamp();
		}

		$this->timestamp = $date;

		// Astronomical constants. 1980 January 0.0
		$epoch = 2444238.5;

		// Constants defining the Sun's apparent orbit
		$elonge = 278.833540;		// Ecliptic longitude of the Sun at epoch 1980.0
		$elongp = 282.596403;		// Ecliptic longitude of the Sun at perigee
		$eccent = 0.016718;			// Eccentricity of Earth's orbit
		$sunsmax = 1.495985e8;		// Semi-major axis of Earth's orbit, km
		$sunangsiz = 0.533128;		// Sun's angular size, degrees, at semi-major axis distance

		// Elements of the Moon's orbit, epoch 1980.0
		$mmlong = 64.975464;		// Moon's mean longitude at the epoch
		$mmlongp = 349.383063;		// Mean longitude of the perigee at the epoch
		$mlnode = 151.950429;		// Mean longitude of the node at the epoch
		$minc = 5.145396;			// Inclination of the Moon's orbit
		$mecc = 0.054900;			// Eccentricity of the Moon's orbit
		$mangsiz = 0.5181;			// Moon's angular size at distance a from Earth
		$msmax = 384401;			// Semi-major axis of Moon's orbit in km
		$mparallax = 0.9507;		// Parallax at distance a from Earth
		$synmonth = 29.53058868;	// Synodic month (new Moon to new Moon)

		$this->synmonth = $synmonth;

		// date is coming in as a UNIX timstamp, so convert it to Julian
		$date = $date / 86400 + 2440587.5;

		// Calculation of the Sun's position
		$Day = $date - $epoch;								// Date within epoch
		$N = $this->fixangle((360 / 365.2422) * $Day);		// Mean anomaly of the Sun
		$M = $this->fixangle($N + $elonge - $elongp);		// Convert from perigee co-ordinates to epoch 1980.0
		$Ec = $this->kepler($M, $eccent);					// Solve equation of Kepler
		$Ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($Ec / 2);
		$Ec = 2 * rad2deg(atan($Ec));						// True anomaly
		$Lambdasun = $this->fixangle($Ec + $elongp);		// Sun's geocentric ecliptic longitude

		$F = ((1 + $eccent * cos(deg2rad($Ec))) / (1 - $eccent * $eccent)); // Orbital distance factor
		$SunDist = $sunsmax / $F;							// Distance to Sun in km
		$SunAng = $F * $sunangsiz;							// Sun's angular size in degrees

		// Calculation of the Moon's position
		$ml = $this->fixangle(13.1763966 * $Day + $mmlong);				// Moon's mean longitude
		$MM = $this->fixangle($ml - 0.1114041 * $Day - $mmlongp);		// Moon's mean anomaly
		$MN = $this->fixangle($mlnode - 0.0529539 * $Day);				// Moon's ascending node mean longitude
		$Ev = 1.2739 * sin(deg2rad(2 * ($ml - $Lambdasun) - $MM));		// Evection
		$Ae = 0.1858 * sin(deg2rad($M));								// Annual equation
		$A3 = 0.37 * sin(deg2rad($M));									// Correction term
		$MmP = $MM + $Ev - $Ae - $A3;									// Corrected anomaly
		$mEc = 6.2886 * sin(deg2rad($MmP));								// Correction for the equation of the centre
		$A4 = 0.214 * sin(deg2rad(2 * $MmP));							// Another correction term
		$lP = $ml + $Ev + $mEc - $Ae + $A4;								// Corrected longitude
		$V = 0.6583 * sin(deg2rad(2 * ($lP - $Lambdasun)));				// Variation
		$lPP = $lP + $V;												// True longitude
		$NP = $MN - 0.16 * sin(deg2rad($M));							// Corrected longitude of the node
		$y = sin(deg2rad($lPP - $NP)) * cos(deg2rad($minc));			// Y inclination coordinate
		$x = cos(deg2rad($lPP - $NP));									// X inclination coordinate

		$Lambdamoon = rad2deg(atan2($y, $x)) + $NP;						// Ecliptic longitude
		$BetaM = rad2deg(asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad($minc)))); // Ecliptic latitude

		// Calculation of the phase of the Moon
		$MoonAge = $lPP - $Lambdasun;									// Age of the Moon in degrees
		$MoonPhase = (1 - cos(deg2rad($MoonAge))) / 2;					// Phase of the Moon

		// Distance of moon from the centre of the Earth
		$MoonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($MmP + $mEc)));

		$MoonDFrac = $MoonDist / $msmax;
		$MoonAng = $mangsiz / $MoonDFrac;								// Moon's angular diameter
		// $MoonPar = $mparallax / $MoonDFrac;							// Moon's parallax

		// Store results
		$this->phase = $this->fixangle($MoonAge) / 360;					// Phase (0 to 1)
		$this->illumination = $MoonPhase;								// Illuminated fraction (0 to 1)
		$this->age = $synmonth * $this->phase;							// Age of moon (days)
		$this->distance = $MoonDist;									// Distance (kilometres)
		$this->diameter = $MoonAng;										// Angular diameter (degrees)
		$this->age_in_degrees = $MoonAge;								//Age of the Moon in degrees
		$this->sundistance = $SunDist;									// Distance to Sun (kilometres)
		$this->sundiameter = $SunAng;									// Sun's angular diameter (degrees)
	}

	/**
     * Fix angle
     *
     * @param float $a
     * @return float
     */
	protected function fixangle(float $a): float
	{
		return ($a - 360 * floor($a / 360));
	}

	/**
     * Kepler
     *
     * @param float $m
     * @param float $ecc
     * @return float
     */
	protected function kepler(float $m, float $ecc): float
	{
		// 1E-6
		$epsilon = 0.000001;
		$e = $m = deg2rad($m);

		do
		{
			$delta = $e - $ecc * sin($e) - $m;
			$e -= $delta / (1 - $ecc * cos($e));
		}
		while (abs($delta) > $epsilon);

		return $e;
	}

	/**
     * Calculates time  of the mean new Moon for a given base date.
     *	 This argument K to this function is the precomputed synodic month index, given by:
     *	 K = (year - 1900) * 12.3685
     *	 where year is expressed as a year and fractional year.
     *
     * @param int   $date
     * @param float $k
     * @return float
     */
	protected function meanphase(int $date, float $k): float
	{
		// Time in Julian centuries from 1900 January 0.5
		$jt = ($date - 2415020.0) / 36525;
		$t2 = $jt * $jt;
		$t3 = $t2 * $jt;

		$nt1 = 2415020.75933 + $this->synmonth * $k
			+ 0.0001178 * $t2
			- 0.000000155 * $t3
			+ 0.00033 * sin(deg2rad(166.56 + 132.87 * $jt - 0.009173 * $t2));

		return $nt1;
	}

	/**
     * Given a K value used to determine the mean phase of the new moon and a
     *	 phase selector (0.0, 0.25, 0.5, 0.75), obtain the true, corrected phase time.
     *
     * @param float $k
     * @param float $phase
     * @return float|null
     */
	protected function truephase(float $k, float $phase): ?float
	{
		$apcor = false;

		$k += $phase;				// Add phase to new moon time
		$t = $k / 1236.85;			// Time in Julian centuries from 1900 January 0.5
		$t2 = $t * $t;				// Square for frequent use
		$t3 = $t2 * $t;				// Cube for frequent use
		$pt = 2415020.75933			// Mean time of phase
			+ $this->synmonth * $k
			+ 0.0001178 * $t2
			- 0.000000155 * $t3
			+ 0.00033 * sin(deg2rad(166.56 + 132.87 * $t - 0.009173 * $t2));

		$m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;			// Sun's mean anomaly
		$mprime = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;	// Moon's mean anomaly
		$f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;			// Moon's argument of latitude

		if ($phase < 0.01 || abs($phase - 0.5) < 0.01)
		{
			// Corrections for New and Full Moon
			$pt += (0.1734 - 0.000393 * $t) * sin(deg2rad($m))
				+ 0.0021 * sin(deg2rad(2 * $m))
				- 0.4068 * sin(deg2rad($mprime))
				+ 0.0161 * sin(deg2rad(2 * $mprime))
				- 0.0004 * sin(deg2rad(3 * $mprime))
				+ 0.0104 * sin(deg2rad(2 * $f))
				- 0.0051 * sin(deg2rad($m + $mprime))
				- 0.0074 * sin(deg2rad($m - $mprime))
				+ 0.0004 * sin(deg2rad(2 * $f + $m))
				- 0.0004 * sin(deg2rad(2 * $f - $m))
				- 0.0006 * sin(deg2rad(2 * $f + $mprime))
				+ 0.0010 * sin(deg2rad(2 * $f - $mprime))
				+ 0.0005 * sin(deg2rad($m + 2 * $mprime));

			$apcor = true;
		}
		else if (abs($phase - 0.25) < 0.01 || abs($phase - 0.75) < 0.01)
		{
			$pt += (0.1721 - 0.0004 * $t) * sin(deg2rad($m))
				+ 0.0021 * sin(deg2rad(2 * $m))
				- 0.6280 * sin(deg2rad($mprime))
				+ 0.0089 * sin(deg2rad(2 * $mprime))
				- 0.0004 * sin(deg2rad(3 * $mprime))
				+ 0.0079 * sin(deg2rad(2 * $f))
				- 0.0119 * sin(deg2rad($m + $mprime))
				- 0.0047 * sin(deg2rad($m - $mprime))
				+ 0.0003 * sin(deg2rad(2 * $f + $m))
				- 0.0004 * sin(deg2rad(2 * $f - $m))
				- 0.0006 * sin(deg2rad(2 * $f + $mprime))
				+ 0.0021 * sin(deg2rad(2 * $f - $mprime))
				+ 0.0003 * sin(deg2rad($m + 2 * $mprime))
				+ 0.0004 * sin(deg2rad($m - 2 * $mprime))
				- 0.0003 * sin(deg2rad(2 * $m + $mprime));

			// First and last quarter corrections
			if ($phase < 0.5)
			{
				$pt += 0.0028 - 0.0004 * cos(deg2rad($m)) + 0.0003 * cos(deg2rad($mprime));
			}
			else
			{
				$pt += -0.0028 + 0.0004 * cos(deg2rad($m)) - 0.0003 * cos(deg2rad($mprime));
			}
			$apcor = true;
		}

		return $apcor ? $pt : null;
	}

	/**
     * Find time of phases of the moon which surround the current date. Five phases are found, starting and
     *	 ending with the new moons which bound the current lunation.
     *
     * @return void
     */
	protected function phasehunt(): void
	{
		$sdate = $this->utc_to_julian($this->timestamp);
		$adate = $sdate - 45;
		$ats = $this->timestamp - 86400 * 45;
		$yy = (int) gmdate('Y', $ats);
		$mm = (int) gmdate('n', $ats);

		$k1 = floor(($yy + (($mm - 1) * (1 / 12)) - 1900) * 12.3685);
		$adate = $nt1 = $this->meanphase($adate, $k1);

		while (true)
		{
			$adate += $this->synmonth;
			$k2 = $k1 + 1;
			$nt2 = $this->meanphase( $adate, $k2 );

			// If nt2 is close to sdate, then mean phase isn't good enough, we have to be more accurate
			if (abs($nt2 - $sdate) < 0.75)
			{
				$nt2 = $this->truephase($k2, 0.0);
			}

			if ($nt1 <= $sdate && $nt2 > $sdate)
			{
				break;
			}

			$nt1 = $nt2;
			$k1 = $k2;
		}

		// Results in Julian dates
		$dates = [
			$this->truephase($k1, 0.0),
			$this->truephase($k1, 0.25),
			$this->truephase($k1, 0.5),
			$this->truephase($k1, 0.75),
			$this->truephase($k2, 0.0),
			$this->truephase($k2, 0.25),
			$this->truephase($k2, 0.5),
			$this->truephase($k2, 0.75)
		];

		$this->quarters = [];
		foreach ($dates as $jdate)
		{
			// Convert to UNIX time
			$this->quarters[] = ($jdate - 2440587.5) * 86400;
		}
	}

    /**
     * UTC to Julian
     *
     * @param int $timestamp
     * @return float
     */
	protected function utc_to_julian(int $timestamp): float
	{
		return $timestamp / 86400 + 2440587.5;
	}

	/**
     * Get moon phase
     *
     * @return float
     */
	public function phase(): float
	{
		return $this->phase;
	}

	/**
     * Get moon properties
     *
     * @param string $property_name
     * @return int|float|array|null
     */
	public function get(string $property_name)
	{
		return $this->{$property_name} ?? null;
	}

	/**
     * Get moon phase data
     *
     * @param string $name
     * @return float
     */
	public function get_phase(string $name): ?float
	{
		$phases = [
			'new_moon',
			'first_quarter',
			'full_moon', 
			'last_quarter',
			'next_new_moon',
			'next_first_quarter',
			'next_full_moon',
			'next_last_quarter',
		];

		if ($this->quarters === false)
		{
			$this->phasehunt();
		}

		return $this->quarters[array_flip($phases)[$name]] ?? null;
	}

	/**
     * Get current phase name
     *	 There are eight phases, evenly split.
     *	 A "New Moon" occupies the 1/16th phases either side of phase = 0, and the rest follow from that.
     *
     * @return string
     */
	public function phase_name(): string
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

		return $names[floor(($this->phase + 0.0625) * 8)];
	}
}
