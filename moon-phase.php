<?php
/**
 * Moon phase calculation class
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw/) 
 * by Samir Shah (http://rayofsolaris.net)
 * Last modified August 2012
 **/

class MoonPhase {
	function __construct($pdate) {
		/*  Astronomical constants  */
		$epoch = 2444238.5;			// 1980 January 0.0
	  
		/*  Constants defining the Sun's apparent orbit  */
		$elonge = 278.833540;		// Ecliptic longitude of the Sun at epoch 1980.0
		$elongp = 282.596403;		// Ecliptic longitude of the Sun at perigee
		$eccent = 0.016718;			// Eccentricity of Earth's orbit
		$sunsmax = 1.495985e8;		// Semi-major axis of Earth's orbit, km
		$sunangsiz = 0.533128;		// Sun's angular size, degrees, at semi-major axis distance
	  
		/*  Elements of the Moon's orbit, epoch 1980.0  */
		$mmlong = 64.975464;		// Moon's mean longitude at the epoch
		$mmlongp = 349.383063;		// Mean longitude of the perigee at the epoch
		$mlnode = 151.950429;		// Mean longitude of the node at the epoch
		$minc = 5.145396;			// Inclination of the Moon's orbit
		$mecc = 0.054900;			// Eccentricity of the Moon's orbit
		$mangsiz = 0.5181;			// Moon's angular size at distance a from Earth
		$msmax = 384401;			// Semi-major axis of Moon's orbit in km
		$mparallax = 0.9507;		// Parallax at distance a from Earth
		$synmonth = 29.53058868;	// Synodic month (new Moon to new Moon)
		$lunatbase = 2423436.0;		// Base date for E. W. Brown's numbered series of lunations (1923 January 16)
	  
		/*  Properties of the Earth  */
		// $earthrad = 6378.16;				// Radius of Earth in kilometres
		// $PI = 3.14159265358979323846;	// Assume not near black hole

		// pdate is coming in as a UNIX timstamp, so convert it to Julian
		$pdate =  $pdate / 86400 + 2440587.5;
		
		/* Calculation of the Sun's position */

		$Day = $pdate - $epoch;								// Date within epoch
		$N = $this->fixangle((360 / 365.2422) * $Day);		// Mean anomaly of the Sun
		$M = $this->fixangle($N + $elonge - $elongp);		// Convert from perigee co-ordinates to epoch 1980.0
		$Ec = $this->kepler($M, $eccent);					// Solve equation of Kepler
		$Ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($Ec / 2);
		$Ec = 2 * rad2deg(atan($Ec));						// True anomaly
		$Lambdasun = $this->fixangle($Ec + $elongp);		// Sun's geocentric ecliptic longitude
		
		$F = ((1 + $eccent * cos(deg2rad($Ec))) / (1 - $eccent * $eccent));	// Orbital distance factor
		$SunDist = $sunsmax / $F;							// Distance to Sun in km
		$SunAng = $F * $sunangsiz;							// Sun's angular size in degrees

		/* Calculation of the Moon's position */
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
		$BetaM = rad2deg(asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad($minc))));		// Ecliptic latitude

		/* Calculation of the phase of the Moon */
		$MoonAge = $lPP - $Lambdasun;									// Age of the Moon in degrees
		$MoonPhase = (1 - cos(deg2rad($MoonAge))) / 2;					// Phase of the Moon

		// Distance of moon from the centre of the Earth
		$MoonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($MmP + $mEc)));

		$MoonDFrac = $MoonDist / $msmax;
		$MoonAng = $mangsiz / $MoonDFrac;								// Moon's angular diameter
		// $MoonPar = $mparallax / $MoonDFrac;							// Moon's parallax
		
		// store results
		$this->phase = $this->fixangle($MoonAge) / 360;					// Phase (0 to 1)
		$this->illum = $MoonPhase;										// Illuminated fraction (0 to 1)
		$this->age = $synmonth * $this->phase;							// Age of moon (days)
		$this->dist = $MoonDist;										// Distance (kilometres)
		$this->angdia = $MoonAng;										// Angular diameter (degrees)
		$this->sundist = $SunDist;										// Distance to Sun (kilometres)
		$this->sunangdia = $SunAng;										// Sun's angular diameter (degrees)
	}
	
	private function fixangle($a) {
		return ( $a - 360 * floor($a / 360) );
	}

	//  KEPLER  --   Solve the equation of Kepler.
	private function kepler($m, $ecc) {
		//double e, delta;
		$epsilon = pow(1, -6);
		$e = $m = deg2rad($m);
		do {
			$delta = $e - $ecc * sin($e) - $m;
			$e -= $delta / ( 1 - $ecc * cos($e) );
		} 
		while ( abs($delta) > $epsilon );
		return $e;
	}
}