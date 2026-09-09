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

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Solaris\MoonPhase;
use Solaris\MoonPhaseName;

final class MoonPhaseNameTest extends TestCase
{
    public function testLabels(): void
    {
        self::assertSame(
            'New Moon',
            MoonPhaseName::NEW_MOON->label()
        );

        self::assertSame(
            'Waxing Crescent',
            MoonPhaseName::WAXING_CRESCENT->label()
        );
    }
}
