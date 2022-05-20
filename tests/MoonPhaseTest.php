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
            1607962725.6397471,
            $moonPhase->getPhaseNewMoon()
        );
    }

    public function testGet1(): void
    {
        $dateTime = new DateTime('2021-01-01');
        $moonPhase = new MoonPhase($dateTime);

        $this->expectDeprecation();
        $this->expectDeprecationMessage('The method `get(\'sundistance\')` has been deprecated. Please use `getSunDistance()` instead.');

        $moonPhase->get('sundistance');
    }

    public function testGet2(): void
    {
        $dateTime = new DateTime('2021-01-01');
        $moonPhase = new MoonPhase($dateTime);

        $value1 = @$moonPhase->get('sundistance');
        $value2 = $moonPhase->getSunDistance();

        self::assertSame(
            $value1,
            $value2
        );
    }
}
