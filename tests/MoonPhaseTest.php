<?php

namespace Solaris\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;

/**
 * Class MoonPhaseTest.
 */
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

        self::assertEquals(
            1_607_962_725.6397471,
            $moonPhase->getPhaseNewMoon()
        );
    }
}
