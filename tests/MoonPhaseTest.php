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

namespace Solaris\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;

class MoonPhaseTest extends TestCase
{
    public function testPhaseName(): void
    {
        $dateTime = new DateTime('2021-01-01');
        $moonPhase = new MoonPhase($dateTime);

        self::assertSame(
            'Full Moon',
            $moonPhase->getPhaseName()
        );
    }

    public function testGetNewMoon(): void
    {
        $dateTime = new DateTime('2021-01-01');
        $moonPhase = new MoonPhase($dateTime);

        self::assertSame(
            1_607_962_725.6397471,
            $moonPhase->getPhaseNewMoon()
        );
    }
}
