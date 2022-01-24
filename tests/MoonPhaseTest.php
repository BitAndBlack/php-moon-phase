<?php

namespace Solaris\Tests;

use DateTime;
use Solaris\MoonPhase;
use PHPUnit\Framework\TestCase;

/**
 * Class MoonPhaseTest.
 */
class MoonPhaseTest extends TestCase
{
    public function testGetPhase(): void
    {
        $dateTime = new DateTime('2021-01-01');
        $moonPhase = new MoonPhase($dateTime);
        
        self::assertSame(
            1607962725.6397471,
            $moonPhase->get_phase('new_moon')
        );
    }

    public function testPhaseName(): void
    {
        $dateTime = new DateTime('2021-01-01');
        $moonPhase = new MoonPhase($dateTime);

        self::assertSame(
            'Full Moon',
            $moonPhase->phase_name()
        );
    }
}
