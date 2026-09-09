<?php

declare(strict_types=1);

/**
 * Solaris PHP Moon Phase. Calculate the phases of the Moon in PHP.
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw).
 *
 * @author Tobias Köngeter <https://www.bitandblack.com>
 * @copyright Copyright © Bit&Black
 * @link https://www.bitandblack.com
 * @license MIT
 */

namespace Solaris\Tests;

use Solaris\MoonPhase;

final class ExposedMoonPhase extends MoonPhase
{
    public function callFixAngle(float $angle): float
    {
        return $this->fixAngle($angle);
    }

    public function callKepler(float $m, float $ecc): float
    {
        return $this->kepler($m, $ecc);
    }

    public function callMeanPhase(int $date, float $k): float
    {
        return $this->meanPhase($date, $k);
    }

    public function callTruePhase(float $k, float $phase): ?float
    {
        return $this->truePhase($k, $phase);
    }

    public function callPhaseHunt(): void
    {
        $this->phaseHunt();
    }

    public function callJulian(int $timestamp): float
    {
        return $this->getJulianFromUTC($timestamp);
    }

    /**
     * @return array<int, float>|null
     */
    public function getQuarters(): ?array
    {
        return $this->quarters;
    }
}
