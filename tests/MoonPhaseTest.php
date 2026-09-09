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

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;
use Solaris\MoonPhaseName;

final class MoonPhaseTest extends TestCase
{
    public function testPhaseName(): void
    {
        $dateTimeImmutable = new DateTimeImmutable('2021-01-01');
        $moonPhase = new MoonPhase($dateTimeImmutable);

        self::assertSame(
            'Full Moon',
            $moonPhase->getPhaseName()
        );
    }

    public function testPhaseNameEnum(): void
    {
        $dateTimeImmutable = new DateTimeImmutable('2021-01-01');
        $moonPhase = new MoonPhase($dateTimeImmutable);

        self::assertSame(
            MoonPhaseName::FULL_MOON,
            $moonPhase->getPhaseNameEnum()
        );
    }

    public function testGetNewMoon(): void
    {
        $dateTimeImmutable = new DateTimeImmutable('2021-01-01');
        $moonPhase = new MoonPhase($dateTimeImmutable);

        self::assertSame(
            1_607_962_725.6397471,
            $moonPhase->getPhaseNewMoon()
        );

        self::assertSame(
            1_607_962_725.6397471,
            $moonPhase->getPhaseByEnum(MoonPhaseName::NEW_MOON)
        );
    }

    public function testDeprecatedGetPhaseNameTriggersDeprecation(): void
    {
        $dateTimeImmutable = new DateTimeImmutable('2021-01-01');
        $moonPhase = new MoonPhase($dateTimeImmutable);

        $errors = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = $errstr;
            return true;
        });

        $moonPhase->getPhaseName();

        restore_error_handler();

        self::assertCount(1, $errors);
        self::assertStringContainsString('getPhaseName() is deprecated', $errors[0]);
        self::assertStringContainsString('getPhaseNameEnum()', $errors[0]);
    }

    public function testDeprecatedGetPhaseByNameTriggersDeprecation(): void
    {
        $dateTimeImmutable = new DateTimeImmutable('2021-01-01');
        $moonPhase = new MoonPhase($dateTimeImmutable);

        $errors = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = $errstr;
            return true;
        });

        $moonPhase->getPhaseByName('new_moon');

        restore_error_handler();

        self::assertCount(1, $errors);
        self::assertStringContainsString('getPhaseByName() is deprecated', $errors[0]);
        self::assertStringContainsString('getPhaseByEnum()', $errors[0]);
    }
}
