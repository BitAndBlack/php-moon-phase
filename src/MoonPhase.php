<?php

/**
 * Solaris PHP Moon Phase. Calculate the phases of the Moon in PHP.
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw).
 *
 * @author Samir Shah <http://rayofsolaris.net>
 * @author Tobias Köngeter <https://www.bitandblack.com>
 * @copyright Copyright © Bit&Black
 * @link https://www.bitandblack.com
 * @license MIT
 */

namespace Solaris;

use DateTimeInterface;

/**
 * @see \Solaris\Tests\MoonPhaseTest
 */
class MoonPhase
{
    protected int $timestamp;

    protected float $phase;

    protected float $illumination;

    protected float $age;

    protected float $distance;

    protected float $diameter;

    protected float $sunDistance;

    protected float $sunDiameter;

    protected float $synmonth;

    /**
     * @var array<int, float>|null
     */
    protected ?array $quarters = null;

    protected float $ageDegrees;

    /**
     * @param DateTimeInterface|null $date
     */
    public function __construct(?DateTimeInterface $date = null)
    {
        $date = null !== $date
            ? $date->getTimestamp()
            : time()
        ;

        $this->timestamp = $date;

        // Astronomical constants. 1980 January 0.0
        $epoch = 2_444_238.5;


        // Constants defining the Sun's apparent orbit

        // Ecliptic longitude of the Sun at epoch 1980.0
        $elonge = 278.833540;

        // Ecliptic longitude of the Sun at perigee
        $elongp = 282.596403;

        // Eccentricity of Earth's orbit
        $eccent = 0.016718;

        // Semi-major axis of Earth's orbit, km
        $sunsmax = 1.495985e8;

        // Sun's angular size, degrees, at semi-major axis distance
        $sunangsiz = 0.533128;


        // Elements of the Moon's orbit, epoch 1980.0

        // Moon's mean longitude at the epoch
        $mmlong = 64.975464;

        // Mean longitude of the perigee at the epoch
        $mmlongp = 349.383063;

        // Mean longitude of the node at the epoch
        // $mlnode = 151.950429;

        // Inclination of the Moon's orbit
        // $minc = 5.145396;

        // Eccentricity of the Moon's orbit
        $mecc = 0.054900;

        // Moon's angular size at distance a from Earth
        $mangsiz = 0.5181;

        // Semi-major axis of Moon's orbit in km
        $msmax = 384401;

        // Parallax at distance a from Earth
        // $mparallax = 0.9507;

        // Synodic month (new Moon to new Moon)
        $synmonth = 29.53058868;

        $this->synmonth = $synmonth;

        // date is coming in as a UNIX timstamp, so convert it to Julian
        $date = $date / 86400 + 2_440_587.5;


        // Calculation of the Sun's position

        // Date within epoch
        $day = $date - $epoch;

        // Mean anomaly of the Sun
        $n = $this->fixAngle((360 / 365.2422) * $day);

        // Convert from perigee co-ordinates to epoch 1980.0
        $m = $this->fixAngle($n + $elonge - $elongp);

        // Solve equation of Kepler
        $ec = $this->kepler($m, $eccent);
        $ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($ec / 2);

        // True anomaly
        $ec = 2 * rad2deg(atan($ec));

        // Sun's geocentric ecliptic longitude
        $lambdaSun = $this->fixAngle($ec + $elongp);

        // Orbital distance factor
        $f = ((1 + $eccent * cos(deg2rad($ec))) / (1 - $eccent * $eccent));

        // Distance to Sun in km
        $sunDist = $sunsmax / $f;

        // Sun's angular size in degrees
        $sunAng = $f * $sunangsiz;


        // Calculation of the Moon's position

        // Moon's mean longitude
        $ml = $this->fixAngle(13.1763966 * $day + $mmlong);

        // Moon's mean anomaly
        $mm = $this->fixAngle($ml - 0.1114041 * $day - $mmlongp);

        // Moon's ascending node mean longitude
        // $MN = $this->fixangle($mlnode - 0.0529539 * $day);

        $evection = 1.2739 * sin(deg2rad(2 * ($ml - $lambdaSun) - $mm));

        $annualEquation = 0.1858 * sin(deg2rad($m));

        // Correction term
        $a3 = 0.37 * sin(deg2rad($m));

        // Corrected anomaly
        $mmp = $mm + $evection - $annualEquation - $a3;

        // Correction for the equation of the centre
        $mEc = 6.2886 * sin(deg2rad($mmp));

        // Another correction term
        $a4 = 0.214 * sin(deg2rad(2 * $mmp));

        // Corrected longitude
        $lP = $ml + $evection + $mEc - $annualEquation + $a4;

        $variation = 0.6583 * sin(deg2rad(2 * ($lP - $lambdaSun)));

        // True longitude
        $lPP = $lP + $variation;

        // Corrected longitude of the node
        // $NP = $MN - 0.16 * sin(deg2rad($m));

        // Y inclination coordinate
        // $y = sin(deg2rad($lPP - $NP)) * cos(deg2rad($minc));

        // X inclination coordinate
        // $x = cos(deg2rad($lPP - $NP));

        // Ecliptic longitude
        // $Lambdamoon = rad2deg(atan2($y, $x)) + $NP;

        // Ecliptic latitude
        // $BetaM = rad2deg(asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad($minc))));


        // Calculation of the phase of the Moon

        // Age of the Moon in degrees
        $moonAge = $lPP - $lambdaSun;

        // Phase of the Moon
        $moonPhase = (1 - cos(deg2rad($moonAge))) / 2;

        // Distance of moon from the centre of the Earth
        $moonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($mmp + $mEc)));

        $moonDFrac = $moonDist / $msmax;

        // Moon's angular diameter
        $moonAng = $mangsiz / $moonDFrac;

        // Moon's parallax
        // $MoonPar = $mparallax / $moonDFrac;


        // Store results

        // Phase (0 to 1)
        $this->phase = $this->fixAngle($moonAge) / 360;

        // Illuminated fraction (0 to 1)
        $this->illumination = $moonPhase;

        // Age of moon (days)
        $this->age = $synmonth * $this->phase;

        // Distance (kilometres)
        $this->distance = $moonDist;

        // Angular diameter (degrees)
        $this->diameter = $moonAng;

        // Age of the Moon in degrees
        $this->ageDegrees = $moonAge;

        // Distance to Sun (kilometres)
        $this->sunDistance = $sunDist;

        // Sun's angular diameter (degrees)
        $this->sunDiameter = $sunAng;
    }

    /**
     * Fix angle
     */
    protected function fixAngle(float $angle): float
    {
        return $angle - 360 * floor($angle / 360);
    }

    /**
     * Kepler
     */
    protected function kepler(float $m, float $ecc): float
    {
        // 1E-6
        $epsilon = 0.000001;
        $e = $m = deg2rad($m);

        do {
            $delta = $e - $ecc * sin($e) - $m;
            $e -= $delta / (1 - $ecc * cos($e));
        } while (abs($delta) > $epsilon);

        return $e;
    }

    /**
     * Calculates time  of the mean new Moon for a given base date.
     * This argument K to this function is the precomputed synodic month index, given by:
     * K = (year - 1900) * 12.3685
     * where year is expressed as a year and fractional year.
     */
    protected function meanPhase(int $date, float $k): float
    {
        // Time in Julian centuries from 1900 January 0.5
        $jt = ($date - 2_415_020.0) / 36525;
        $t2 = $jt * $jt;
        $t3 = $t2 * $jt;

        $nt1 = 2_415_020.75933 + $this->synmonth * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * sin(deg2rad(166.56 + 132.87 * $jt - 0.009173 * $t2))
        ;

        return $nt1;
    }

    /**
     * Given a K value used to determine the mean phase of the new moon and a
     * phase selector (0.0, 0.25, 0.5, 0.75), obtain the true, corrected phase time.
     */
    protected function truePhase(float $k, float $phase): ?float
    {
        $apcor = false;

        // Add phase to new moon time
        $k += $phase;

        // Time in Julian centuries from 1900 January 0.5
        $t = $k / 1236.85;

        // Square for frequent use
        $t2 = $t * $t;

        // Cube for frequent use
        $t3 = $t2 * $t;

        // Mean time of phase
        $pt = 2_415_020.75933
            + $this->synmonth * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * sin(deg2rad(166.56 + 132.87 * $t - 0.009173 * $t2))
        ;

        // Sun's mean anomaly
        $m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;

        // Moon's mean anomaly
        $mprime = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;

        // Moon's argument of latitude
        $f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;

        if ($phase < 0.01 || abs($phase - 0.5) < 0.01) {
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
                + 0.0005 * sin(deg2rad($m + 2 * $mprime))
            ;

            $apcor = true;
        } elseif (abs($phase - 0.25) < 0.01 || abs($phase - 0.75) < 0.01) {
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
                - 0.0003 * sin(deg2rad(2 * $m + $mprime))
            ;

            // First and last quarter corrections
            if ($phase < 0.5) {
                $pt += 0.0028 - 0.0004 * cos(deg2rad($m)) + 0.0003 * cos(deg2rad($mprime));
            } else {
                $pt += -0.0028 + 0.0004 * cos(deg2rad($m)) - 0.0003 * cos(deg2rad($mprime));
            }

            $apcor = true;
        }

        return $apcor ? $pt : null;
    }

    /**
     * Find time of phases of the moon which surround the current date. Five phases are found, starting and
     * ending with the new moons which bound the current lunation.
     */
    protected function phaseHunt(): void
    {
        $sdate = $this->getJulianFromUTC($this->timestamp);
        $adate = $sdate - 45;
        $ats = $this->timestamp - 86400 * 45;
        $yy = (int) gmdate('Y', $ats);
        $mm = (int) gmdate('n', $ats);

        $k1 = floor(($yy + (($mm - 1) * (1 / 12)) - 1900) * 12.3685);
        $adate = $nt1 = $this->meanPhase((int) $adate, $k1);

        while (true) {
            $adate += $this->synmonth;
            $k2 = $k1 + 1;
            $nt2 = $this->meanPhase((int) $adate, $k2);

            // If nt2 is close to sdate, then mean phase isn't good enough, we have to be more accurate
            if (abs($nt2 - $sdate) < 0.75) {
                $nt2 = $this->truePhase($k2, 0.0);
            }

            if ($nt1 <= $sdate && $nt2 > $sdate) {
                break;
            }

            $nt1 = $nt2;
            $k1 = $k2;
        }

        // Results in Julian dates
        $dates = [
            $this->truePhase($k1, 0.0),
            $this->truePhase($k1, 0.25),
            $this->truePhase($k1, 0.5),
            $this->truePhase($k1, 0.75),
            $this->truePhase($k2, 0.0),
            $this->truePhase($k2, 0.25),
            $this->truePhase($k2, 0.5),
            $this->truePhase($k2, 0.75),
        ];

        $this->quarters = [];

        foreach ($dates as $jdate) {
            // Convert to UNIX time
            $this->quarters[] = ($jdate - 2_440_587.5) * 86400;
        }
    }

    /**
     * UTC to Julian
     */
    protected function getJulianFromUTC(int $timestamp): float
    {
        return $timestamp / 86400 + 2_440_587.5;
    }

    /**
     * Returns the moon phase.
     */
    public function getPhase(): float
    {
        return $this->phase;
    }

    public function getIllumination(): float
    {
        return $this->illumination;
    }

    public function getAge(): float
    {
        return $this->age;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function getDiameter(): float
    {
        return $this->diameter;
    }

    public function getSunDistance(): float
    {
        return $this->sunDistance;
    }

    public function getSunDiameter(): float
    {
        return $this->sunDiameter;
    }

    /**
     * Get moon phase data
     */
    public function getPhaseByName(string $name): ?float
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

        if (null === $this->quarters) {
            $this->phaseHunt();
        }

        return $this->quarters[array_flip($phases)[$name]] ?? null;
    }

    /**
     * Get current phase name. There are eight phases, evenly split.
     * A "New Moon" occupies the 1/16th phases either side of phase = 0, and the rest follow from that.
     */
    public function getPhaseName(): string
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

    public function getPhaseNewMoon(): ?float
    {
        return $this->getPhaseByName('new_moon');
    }

    public function getPhaseFirstQuarter(): ?float
    {
        return $this->getPhaseByName('first_quarter');
    }

    public function getPhaseFullMoon(): ?float
    {
        return $this->getPhaseByName('full_moon');
    }

    public function getPhaseLastQuarter(): ?float
    {
        return $this->getPhaseByName('last_quarter');
    }

    public function getPhaseNextNewMoon(): ?float
    {
        return $this->getPhaseByName('next_new_moon');
    }

    public function getPhaseNextFirstQuarter(): ?float
    {
        return $this->getPhaseByName('next_first_quarter');
    }

    public function getPhaseNextFullMoon(): ?float
    {
        return $this->getPhaseByName('next_full_moon');
    }

    public function getPhaseNextLastQuarter(): ?float
    {
        return $this->getPhaseByName('next_last_quarter');
    }
}
