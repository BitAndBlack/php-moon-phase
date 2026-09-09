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

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Calculates the phases of the Moon for a given point in time.
 *
 * Once instantiated, the object exposes measurements such as the illuminated
 * fraction, the age and distance of the Moon, as well as the exact times of the
 * lunar quarters surrounding the given date. Quarter times are returned either
 * as UNIX timestamps (float) or as {@see DateTimeImmutable} objects in UTC.
 *
 * @see \Solaris\Tests\MoonPhaseTest
 */
class MoonPhase
{
    /**
     * The UNIX timestamp of the moment this instance was created for.
     */
    protected int $timestamp;

    /**
     * The terminator phase angle as a fraction of a full circle (0 to 1).
     */
    protected float $phase;

    /**
     * The illuminated fraction of the Moon's disc (0 to 1).
     */
    protected float $illumination;

    /**
     * The age of the Moon in days.
     */
    protected float $age;

    /**
     * The distance between the Moon and the centre of the Earth in kilometres.
     */
    protected float $distance;

    /**
     * The angular diameter subtended by the Moon in degrees.
     */
    protected float $diameter;

    /**
     * The distance between the Sun and the Earth in kilometres.
     */
    protected float $sunDistance;

    /**
     * The angular diameter subtended by the Sun in degrees.
     */
    protected float $sunDiameter;

    /**
     * The length of the synodic month (New Moon to New Moon) in days.
     */
    protected float $synmonth;

    /**
     * The UNIX timestamps of the eight lunar quarters surrounding the given date,
     * or null if they have not been computed yet.
     *
     * Indexes 0 to 3 are the New Moon, first quarter, Full Moon and last
     * quarter of the current lunation. Indexes 4 to 7 are those of the next lunation.
     *
     * @var array<int, float>|null
     */
    protected ?array $quarters = null;

    /**
     * The age of the Moon in degrees.
     */
    protected float $ageDegrees;

    /**
     * Creates a new MoonPhase instance for the given point in time.
     *
     * @param DateTimeInterface|null $date The point in time to calculate the moon phase for. If omitted, the current time is used.
     */
    public function __construct(?DateTimeInterface $date = null)
    {
        $date = $date instanceof DateTimeInterface
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
     * Normalizes an angle to the range 0 (inclusive) to 360 (exclusive) degrees.
     *
     * @param float $angle the angle to normalize, in degrees
     * @return float the equivalent angle, in degrees, within 0 .. 360
     */
    protected function fixAngle(float $angle): float
    {
        return $angle - 360 * floor($angle / 360);
    }

    /**
     * Solves Kepler's equation for the eccentric anomaly using Newton's method.
     *
     * @param float $m   the mean anomaly, in degrees
     * @param float $ecc the orbital eccentricity
     * @return float the eccentric anomaly, in radians
     */
    protected function kepler(float $m, float $ecc): float
    {
        // 1E-6
        $epsilon = 0.000001;
        $e = deg2rad($m);
        $m = $e;

        do {
            $delta = $e - $ecc * sin($e) - $m;
            $e -= $delta / (1 - $ecc * cos($e));
        } while (abs($delta) > $epsilon);

        return $e;
    }

    /**
     * Calculates the time of the mean New Moon for a given synodic month index.
     *
     * @param int $date the reference date as a Julian date
     * @param float $k the precomputed synodic month index, given by `K = (year - 1900) * 12.3685`, where `year` is expressed as a year and fractional year
     * @return float the mean New Moon time as a Julian date
     */
    protected function meanPhase(int $date, float $k): float
    {
        // Time in Julian centuries from 1900 January 0.5
        $jt = ($date - 2_415_020.0) / 36525;
        $t2 = $jt * $jt;
        $t3 = $t2 * $jt;

        return 2_415_020.75933 + $this->synmonth * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * sin(deg2rad(166.56 + 132.87 * $jt - 0.009173 * $t2));
    }

    /**
     * Returns the true, corrected time of a lunar phase for a given synodic month index.
     *
     * @param float $k the synodic month index
     * @param float $phase the phase selector: 0.0 (New Moon), 0.25 (First Quarter), 0.5 (Full Moon) or 0.75 (Last Quarter)
     * @return float|null the corrected phase time as a Julian date, or null if the phase selector is not one of the four quarters
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
     * Finds the times of the lunar phases which surround the current date and caches them in the `quarters` property.
     *
     * The two New Moons that bound the current lunation are found, then the New Moon, first quarter,
     * Full Moon and last quarter are computed for the current and the following lunation.
     * All eight results are stored as UNIX timestamps.
     */
    protected function phaseHunt(): void
    {
        $sdate = $this->getJulianFromUTC($this->timestamp);
        $adate = $sdate - 45;
        $ats = $this->timestamp - 86400 * 45;
        $yy = (int) gmdate('Y', $ats);
        $mm = (int) gmdate('n', $ats);

        $k1 = floor(($yy + (($mm - 1) * (1 / 12)) - 1900) * 12.3685);
        $adate = $this->meanPhase((int) $adate, $k1);
        $nt1 = $adate;

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
     * Converts a UNIX timestamp into a Julian date.
     *
     * @param int $timestamp the UNIX timestamp
     * @return float the Julian date
     */
    protected function getJulianFromUTC(int $timestamp): float
    {
        return $timestamp / 86400 + 2_440_587.5;
    }

    /**
     * Returns the terminator phase angle as a fraction of a full circle.
     *
     * The value ranges from 0 to 1, where both 0 and 1 correspond to a New Moon and 0.5 corresponds to a Full Moon.
     *
     * @return float the phase angle of the Moon, from 0 (New Moon) to 1
     */
    public function getPhase(): float
    {
        return $this->phase;
    }

    /**
     * Returns the illuminated fraction of the Moon's disc.
     *
     * @return float the illuminated fraction, from 0 (New Moon) to 1 (Full Moon)
     */
    public function getIllumination(): float
    {
        return $this->illumination;
    }

    /**
     * Returns the age of the Moon in days since the last New Moon.
     *
     * @return float the age of the Moon in days
     */
    public function getAge(): float
    {
        return $this->age;
    }

    /**
     * Returns the distance between the Moon and the centre of the Earth.
     *
     * @return float the distance in kilometres
     */
    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * Returns the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth.
     *
     * @return float the angular diameter in degrees
     */
    public function getDiameter(): float
    {
        return $this->diameter;
    }

    /**
     * Returns the distance between the Sun and the centre of the Earth.
     *
     * @return float the distance in kilometres
     */
    public function getSunDistance(): float
    {
        return $this->sunDistance;
    }

    /**
     * Returns the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth.
     *
     * @return float the angular diameter in degrees
     */
    public function getSunDiameter(): float
    {
        return $this->sunDiameter;
    }

    /**
     * Returns the UNIX timestamp of a lunar phase by its string key.
     *
     * @param string $name the phase key, one of 'new_moon', 'first_quarter', 'full_moon', 'last_quarter' or the corresponding 'next_*' variant
     * @return float|null the UNIX timestamp of the given phase, or null if the name is unknown
     * @deprecated Use {@see getPhaseByEnum()} instead.
     * @todo Remove in v4.0.
     */
    public function getPhaseByName(string $name): ?float
    {
        trigger_error(
            sprintf(
                '%s() is deprecated and will be removed in the next major release. Please use %s() instead.',
                __METHOD__,
                'getPhaseByEnum'
            ),
            E_USER_DEPRECATED
        );

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
     * Returns the UNIX timestamp of a lunar phase.
     *
     * Only the phases that correspond to a lunar quarter have a timestamp: the New Moon, first quarter, Full Moon
     * and last quarter of the current lunation, as well as those of the next lunation (the `NEXT_*` enum cases).
     * The purely visual phases return null.
     *
     * @param MoonPhaseName $phase the phase to look up
     * @return float|null the UNIX timestamp of the given phase, or null if the phase has no quarter time
     */
    public function getPhaseByEnum(MoonPhaseName $phase): ?float
    {
        if (null === $this->quarters) {
            $this->phaseHunt();
        }

        $quarters = [
            MoonPhaseName::NEW_MOON->value => 0,
            MoonPhaseName::FIRST_QUARTER->value => 1,
            MoonPhaseName::FULL_MOON->value => 2,
            MoonPhaseName::THIRD_QUARTER->value => 3,
            MoonPhaseName::NEXT_NEW_MOON->value => 4,
            MoonPhaseName::NEXT_FIRST_QUARTER->value => 5,
            MoonPhaseName::NEXT_FULL_MOON->value => 6,
            MoonPhaseName::NEXT_LAST_QUARTER->value => 7,
        ];

        $index = $quarters[$phase->value] ?? null;

        if (null === $index) {
            return null;
        }

        return $this->quarters[$index] ?? null;
    }

    /**
     * Returns a lunar phase as a DateTimeImmutable object in UTC.
     *
     * @param MoonPhaseName $phase the phase to look up
     * @return DateTimeImmutable the phase time as a DateTimeImmutable object, preserving fractional seconds
     * @throws Exception if the given phase has no quarter time
     */
    public function getPhaseByEnumDateTime(MoonPhaseName $phase): DateTimeImmutable
    {
        $timestamp = $this->getPhaseByEnum($phase);

        if (null === $timestamp) {
            throw new Exception(sprintf('No phase data available for %s.', $phase->value));
        }

        $dateTime = DateTimeImmutable::createFromFormat('U.u', sprintf('%.6f', $timestamp), new DateTimeZone('UTC'));

        if (false === $dateTime) {
            throw new Exception(sprintf('Could not convert timestamp %f to a date.', $timestamp));
        }

        return $dateTime;
    }

    /**
     * Returns the name of the current phase.
     *
     * @return string the phase name, e.g. 'Full Moon'
     * @deprecated Use {@see getPhaseNameEnum()} instead.
     * @todo Remove in v4.0.
     */
    public function getPhaseName(): string
    {
        trigger_error(
            sprintf(
                '%s() is deprecated and will be removed in the next major release. Please use %s() instead.',
                __METHOD__,
                'getPhaseNameEnum'
            ),
            E_USER_DEPRECATED
        );

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
     * Returns the current phase as a {@see MoonPhaseName} enum.
     *
     * There are eight phases, evenly split. A "New Moon" occupies the 1/16th
     * phases either side of phase = 0, and the rest follow from that.
     *
     * @return MoonPhaseName the current phase
     */
    public function getPhaseNameEnum(): MoonPhaseName
    {
        $names = [
            MoonPhaseName::NEW_MOON,
            MoonPhaseName::WAXING_CRESCENT,
            MoonPhaseName::FIRST_QUARTER,
            MoonPhaseName::WAXING_GIBBOUS,
            MoonPhaseName::FULL_MOON,
            MoonPhaseName::WANING_GIBBOUS,
            MoonPhaseName::THIRD_QUARTER,
            MoonPhaseName::WANING_CRESCENT,
            MoonPhaseName::NEW_MOON,
        ];

        return $names[(int) floor(($this->phase + 0.0625) * 8)];
    }

    /**
     * Returns the UNIX timestamp of the New Moon in the current lunar cycle.
     *
     * @return float|null the UNIX timestamp of the New Moon
     */
    public function getPhaseNewMoon(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::NEW_MOON);
    }

    /**
     * Returns the UNIX timestamp of the first quarter in the current lunar cycle.
     *
     * @return float|null the UNIX timestamp of the first quarter
     */
    public function getPhaseFirstQuarter(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::FIRST_QUARTER);
    }

    /**
     * Returns the UNIX timestamp of the Full Moon in the current lunar cycle.
     *
     * @return float|null the UNIX timestamp of the Full Moon
     */
    public function getPhaseFullMoon(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::FULL_MOON);
    }

    /**
     * Returns the UNIX timestamp of the last quarter in the current lunar cycle.
     *
     * @return float|null the UNIX timestamp of the last quarter
     */
    public function getPhaseLastQuarter(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::THIRD_QUARTER);
    }

    /**
     * Returns the UNIX timestamp of the New Moon in the next lunar cycle.
     *
     * @return float|null the UNIX timestamp of the New Moon
     */
    public function getPhaseNextNewMoon(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::NEXT_NEW_MOON);
    }

    /**
     * Returns the UNIX timestamp of the first quarter in the next lunar cycle.
     *
     * @return float|null the UNIX timestamp of the first quarter
     */
    public function getPhaseNextFirstQuarter(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::NEXT_FIRST_QUARTER);
    }

    /**
     * Returns the UNIX timestamp of the Full Moon in the next lunar cycle.
     *
     * @return float|null the UNIX timestamp of the Full Moon
     */
    public function getPhaseNextFullMoon(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::NEXT_FULL_MOON);
    }

    /**
     * Returns the UNIX timestamp of the last quarter in the next lunar cycle.
     *
     * @return float|null the UNIX timestamp of the last quarter
     */
    public function getPhaseNextLastQuarter(): ?float
    {
        return $this->getPhaseByEnum(MoonPhaseName::NEXT_LAST_QUARTER);
    }

    /**
     * Returns the New Moon in the current lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the New Moon time
     * @throws Exception
     */
    public function getPhaseNewMoonDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::NEW_MOON);
    }

    /**
     * Returns the first quarter in the current lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the first quarter time
     * @throws Exception
     */
    public function getPhaseFirstQuarterDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::FIRST_QUARTER);
    }

    /**
     * Returns the Full Moon in the current lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the Full Moon time
     * @throws Exception
     */
    public function getPhaseFullMoonDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::FULL_MOON);
    }

    /**
     * Returns the last quarter in the current lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the last quarter time
     * @throws Exception
     */
    public function getPhaseLastQuarterDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::THIRD_QUARTER);
    }

    /**
     * Returns the New Moon in the next lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the New Moon time
     * @throws Exception
     */
    public function getPhaseNextNewMoonDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::NEXT_NEW_MOON);
    }

    /**
     * Returns the first quarter in the next lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the first quarter time
     * @throws Exception
     */
    public function getPhaseNextFirstQuarterDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::NEXT_FIRST_QUARTER);
    }

    /**
     * Returns the Full Moon in the next lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the Full Moon time
     * @throws Exception
     */
    public function getPhaseNextFullMoonDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::NEXT_FULL_MOON);
    }

    /**
     * Returns the last quarter in the next lunar cycle as a DateTimeImmutable object in UTC.
     *
     * @return DateTimeImmutable the last quarter time
     * @throws Exception
     */
    public function getPhaseNextLastQuarterDateTime(): DateTimeImmutable
    {
        return $this->getPhaseByEnumDateTime(MoonPhaseName::NEXT_LAST_QUARTER);
    }
}
