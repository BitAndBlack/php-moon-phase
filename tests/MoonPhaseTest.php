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
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;
use Solaris\MoonPhaseName;

final class MoonPhaseTest extends TestCase
{
    private MoonPhase $moonPhase;

    protected function setUp(): void
    {
        $this->moonPhase = new MoonPhase(
            new DateTimeImmutable('2021-01-01')
        );
    }

    public function testGetPhase(): void
    {
        self::assertSame(
            0.56100063497196373,
            $this->moonPhase->getPhase()
        );
    }

    public function testGetIllumination(): void
    {
        self::assertSame(
            0.9637218306811195,
            $this->moonPhase->getIllumination()
        );
    }

    public function testGetAge(): void
    {
        self::assertSame(
            16.566679000575885,
            $this->moonPhase->getAge()
        );
    }

    public function testGetDistance(): void
    {
        self::assertSame(
            385284.32531933993,
            $this->moonPhase->getDistance()
        );
    }

    public function testGetDiameter(): void
    {
        self::assertSame(
            0.5169121737172393,
            $this->moonPhase->getDiameter()
        );
    }

    public function testGetSunDistance(): void
    {
        self::assertSame(
            147098681.50351453,
            $this->moonPhase->getSunDistance()
        );
    }

    public function testGetSunDiameter(): void
    {
        self::assertSame(
            0.54218806241369655,
            $this->moonPhase->getSunDiameter()
        );
    }

    /**
     * @return Iterator<string, array{string, float}>
     */
    public static function providePhaseNames(): Iterator
    {
        yield 'new_moon' => ['new_moon', 1_607_962_725.6397471];
        yield 'first_quarter' => ['first_quarter', 1_608_594_151.3786912];
        yield 'full_moon' => ['full_moon', 1_609_299_024.9573112];
        yield 'last_quarter' => ['last_quarter', 1_609_925_915.9353762];
        yield 'next_new_moon' => ['next_new_moon', 1_610_514_157.8635306];
        yield 'next_first_quarter' => ['next_first_quarter', 1_611_176_615.5484586];
        yield 'next_full_moon' => ['next_full_moon', 1_611_861_515.6238689];
        yield 'next_last_quarter' => ['next_last_quarter', 1_612_460_322.4647045];
    }

    #[DataProvider('providePhaseNames')]
    public function testGetPhaseByName(string $name, float $expected): void
    {
        self::assertSame(
            $expected,
            $this->withSuppressedDeprecations(fn (): ?float => $this->moonPhase->getPhaseByName($name))
        );
    }

    /**
     * @return Iterator<string, array{MoonPhaseName, float}>
     */
    public static function providePhaseEnums(): Iterator
    {
        yield 'new_moon' => [MoonPhaseName::NEW_MOON, 1_607_962_725.6397471];
        yield 'first_quarter' => [MoonPhaseName::FIRST_QUARTER, 1_608_594_151.3786912];
        yield 'full_moon' => [MoonPhaseName::FULL_MOON, 1_609_299_024.9573112];
        yield 'third_quarter' => [MoonPhaseName::THIRD_QUARTER, 1_609_925_915.9353762];
        yield 'next_new_moon' => [MoonPhaseName::NEXT_NEW_MOON, 1_610_514_157.8635306];
        yield 'next_first_quarter' => [MoonPhaseName::NEXT_FIRST_QUARTER, 1_611_176_615.5484586];
        yield 'next_full_moon' => [MoonPhaseName::NEXT_FULL_MOON, 1_611_861_515.6238689];
        yield 'next_last_quarter' => [MoonPhaseName::NEXT_LAST_QUARTER, 1_612_460_322.4647045];
    }

    #[DataProvider('providePhaseEnums')]
    public function testGetPhaseByEnum(MoonPhaseName $phase, float $expected): void
    {
        self::assertSame($expected, $this->moonPhase->getPhaseByEnum($phase));
    }

    /**
     * @return Iterator<string, array{MoonPhaseName}>
     */
    public static function provideVisualPhases(): Iterator
    {
        yield 'waxing_crescent' => [MoonPhaseName::WAXING_CRESCENT];
        yield 'waxing_gibbous' => [MoonPhaseName::WAXING_GIBBOUS];
        yield 'waning_gibbous' => [MoonPhaseName::WANING_GIBBOUS];
        yield 'waning_crescent' => [MoonPhaseName::WANING_CRESCENT];
    }

    #[DataProvider('provideVisualPhases')]
    public function testGetPhaseByEnumReturnsNullForVisualPhases(MoonPhaseName $phase): void
    {
        self::assertNull(
            $this->moonPhase->getPhaseByEnum($phase)
        );
    }

    public function testGetPhaseByNameReturnsNullForUnknownName(): void
    {
        self::assertNull(
            $this->withSuppressedDeprecations(fn (): ?float => $this->moonPhase->getPhaseByName('unknown_phase'))
        );
    }

    public function testPhaseName(): void
    {
        self::assertSame(
            'Full Moon',
            $this->withSuppressedDeprecations(fn (): string => $this->moonPhase->getPhaseName())
        );
    }

    public function testPhaseNameEnum(): void
    {
        self::assertSame(
            MoonPhaseName::FULL_MOON,
            $this->moonPhase->getPhaseNameEnum()
        );
    }

    /**
     * @return Iterator<string, array{MoonPhaseName, float}>
     */
    public static function provideConvenienceGetters(): Iterator
    {
        yield 'getPhaseNewMoon' => [MoonPhaseName::NEW_MOON, 1_607_962_725.6397471];
        yield 'getPhaseFirstQuarter' => [MoonPhaseName::FIRST_QUARTER, 1_608_594_151.3786912];
        yield 'getPhaseFullMoon' => [MoonPhaseName::FULL_MOON, 1_609_299_024.9573112];
        yield 'getPhaseLastQuarter' => [MoonPhaseName::THIRD_QUARTER, 1_609_925_915.9353762];
        yield 'getPhaseNextNewMoon' => [MoonPhaseName::NEXT_NEW_MOON, 1_610_514_157.8635306];
        yield 'getPhaseNextFirstQuarter' => [MoonPhaseName::NEXT_FIRST_QUARTER, 1_611_176_615.5484586];
        yield 'getPhaseNextFullMoon' => [MoonPhaseName::NEXT_FULL_MOON, 1_611_861_515.6238689];
        yield 'getPhaseNextLastQuarter' => [MoonPhaseName::NEXT_LAST_QUARTER, 1_612_460_322.4647045];
    }

    #[DataProvider('provideConvenienceGetters')]
    public function testConvenienceGetters(MoonPhaseName $phase, float $expected): void
    {
        $method = match ($phase) {
            MoonPhaseName::NEW_MOON => 'getPhaseNewMoon',
            MoonPhaseName::FIRST_QUARTER => 'getPhaseFirstQuarter',
            MoonPhaseName::FULL_MOON => 'getPhaseFullMoon',
            MoonPhaseName::THIRD_QUARTER => 'getPhaseLastQuarter',
            MoonPhaseName::NEXT_NEW_MOON => 'getPhaseNextNewMoon',
            MoonPhaseName::NEXT_FIRST_QUARTER => 'getPhaseNextFirstQuarter',
            MoonPhaseName::NEXT_FULL_MOON => 'getPhaseNextFullMoon',
            MoonPhaseName::NEXT_LAST_QUARTER => 'getPhaseNextLastQuarter',
            default => self::fail(sprintf('Unexpected phase: %s.', $phase->value)),
        };

        self::assertSame(
            $expected,
            $this->moonPhase->{$method}()
        );
    }

    public function testProtectedFixAngle(): void
    {
        $moonPhase = $this->createExposedMoonPhase();

        self::assertSame(40.0, $moonPhase->callFixAngle(400.0));
        self::assertSame(350.0, $moonPhase->callFixAngle(-10.0));
        self::assertSame(0.5, $moonPhase->callFixAngle(360.5));
    }

    public function testProtectedKepler(): void
    {
        $moonPhase = $this->createExposedMoonPhase();

        self::assertSame(0.0, $moonPhase->callKepler(0.0, 0.0));
        self::assertSame(0.0, $moonPhase->callKepler(0.0, 0.0549));
        self::assertSame(0.09232807552341715, $moonPhase->callKepler(5.0, 0.0549));
    }

    public function testProtectedMeanPhase(): void
    {
        $moonPhase = $this->createExposedMoonPhase();

        self::assertSame(
            2_459_080.3976479005,
            $moonPhase->callMeanPhase(2_459_735, 1492.0)
        );
    }

    public function testProtectedTruePhase(): void
    {
        $moonPhase = $this->createExposedMoonPhase();
        $newMoon = $moonPhase->callTruePhase(1492.0, 0.0);

        self::assertIsFloat($newMoon);

        $firstQuarter = $moonPhase->callTruePhase(1492.0, 0.25);

        self::assertIsFloat($firstQuarter);
        self::assertGreaterThan($newMoon, $firstQuarter);
        self::assertNull($moonPhase->callTruePhase(1492.0, 0.1));
    }

    public function testProtectedPhaseHunt(): void
    {
        $moonPhase = $this->createExposedMoonPhase();

        self::assertNull($moonPhase->getQuarters());

        $moonPhase->callPhaseHunt();

        self::assertNotNull($moonPhase->getQuarters());
        self::assertCount(8, $moonPhase->getQuarters());
    }

    public function testProtectedGetJulianFromUTC(): void
    {
        $moonPhase = $this->createExposedMoonPhase();

        self::assertSame(2_440_587.5, $moonPhase->callJulian(0));
    }

    public function testDeprecatedGetPhaseNameTriggersDeprecation(): void
    {
        $errors = $this->captureDeprecations(function (): void {
            $this->moonPhase->getPhaseName();
        });

        self::assertCount(1, $errors);
        self::assertStringContainsString('getPhaseName() is deprecated', $errors[0]);
        self::assertStringContainsString('getPhaseNameEnum()', $errors[0]);
    }

    public function testDeprecatedGetPhaseByNameTriggersDeprecation(): void
    {
        $errors = $this->captureDeprecations(function (): void {
            $this->moonPhase->getPhaseByName('new_moon');
        });

        self::assertCount(1, $errors);
        self::assertStringContainsString('getPhaseByName() is deprecated', $errors[0]);
        self::assertStringContainsString('getPhaseByEnum()', $errors[0]);
    }

    private function createExposedMoonPhase(): ExposedMoonPhase
    {
        return new ExposedMoonPhase(new DateTimeImmutable('2021-01-01'));
    }

    /**
     * @return string[]
     */
    private function captureDeprecations(callable $callback): array
    {
        $errors = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = $errstr;

            return true;
        });

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $errors;
    }

    private function withSuppressedDeprecations(callable $callback): mixed
    {
        set_error_handler(static fn (int $errno): bool => true);

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }
}
