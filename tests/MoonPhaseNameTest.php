<?php

declare(strict_types=1);

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

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhaseName;

final class MoonPhaseNameTest extends TestCase
{
    /**
     * @return Iterator<string, array{MoonPhaseName, string, string}>
     */
    public static function provideCases(): Iterator
    {
        yield 'new_moon' => [MoonPhaseName::NEW_MOON, 'new_moon', 'New Moon'];
        yield 'waxing_crescent' => [MoonPhaseName::WAXING_CRESCENT, 'waxing_crescent', 'Waxing Crescent'];
        yield 'first_quarter' => [MoonPhaseName::FIRST_QUARTER, 'first_quarter', 'First Quarter'];
        yield 'waxing_gibbous' => [MoonPhaseName::WAXING_GIBBOUS, 'waxing_gibbous', 'Waxing Gibbous'];
        yield 'full_moon' => [MoonPhaseName::FULL_MOON, 'full_moon', 'Full Moon'];
        yield 'waning_gibbous' => [MoonPhaseName::WANING_GIBBOUS, 'waning_gibbous', 'Waning Gibbous'];
        yield 'third_quarter' => [MoonPhaseName::THIRD_QUARTER, 'third_quarter', 'Third Quarter'];
        yield 'waning_crescent' => [MoonPhaseName::WANING_CRESCENT, 'waning_crescent', 'Waning Crescent'];
        yield 'next_new_moon' => [MoonPhaseName::NEXT_NEW_MOON, 'next_new_moon', 'New Moon'];
        yield 'next_first_quarter' => [MoonPhaseName::NEXT_FIRST_QUARTER, 'next_first_quarter', 'First Quarter'];
        yield 'next_full_moon' => [MoonPhaseName::NEXT_FULL_MOON, 'next_full_moon', 'Full Moon'];
        yield 'next_last_quarter' => [MoonPhaseName::NEXT_LAST_QUARTER, 'next_last_quarter', 'Third Quarter'];
    }

    #[DataProvider('provideCases')]
    public function testValue(MoonPhaseName $phase, string $value): void
    {
        self::assertSame($value, $phase->value);
    }

    #[DataProvider('provideCases')]
    public function testLabel(MoonPhaseName $phase, string $value, string $label): void
    {
        self::assertSame($label, $phase->label());
    }
}
