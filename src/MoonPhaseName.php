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

namespace Solaris;

/**
 * Represents the phases of the Moon.
 *
 * The eight plain cases describe the appearance of the Moon as seen from Earth,
 * from the New Moon to the Waning Crescent. The four `NEXT_*` cases refer to the
 * next occurrence of the four main phases and are used to look up quarter times.
 *
 * Each case has a machine-readable, snake-cased backing value (`->value`) and a
 * human-readable name returned by {@see MoonPhaseName::label()}.
 */
enum MoonPhaseName: string
{
    case NEW_MOON = 'new_moon';

    case WAXING_CRESCENT = 'waxing_crescent';

    case FIRST_QUARTER = 'first_quarter';

    case WAXING_GIBBOUS = 'waxing_gibbous';

    case FULL_MOON = 'full_moon';

    case WANING_GIBBOUS = 'waning_gibbous';

    case THIRD_QUARTER = 'third_quarter';

    case WANING_CRESCENT = 'waning_crescent';

    case NEXT_NEW_MOON = 'next_new_moon';

    case NEXT_FIRST_QUARTER = 'next_first_quarter';

    case NEXT_FULL_MOON = 'next_full_moon';

    case NEXT_LAST_QUARTER = 'next_last_quarter';

    /**
     * Returns the human-readable name of the phase.
     *
     * @return string the phase label, e.g. 'Full Moon'
     */
    public function label(): string
    {
        return match ($this) {
            self::NEW_MOON, self::NEXT_NEW_MOON => 'New Moon',
            self::WAXING_CRESCENT => 'Waxing Crescent',
            self::FIRST_QUARTER, self::NEXT_FIRST_QUARTER => 'First Quarter',
            self::WAXING_GIBBOUS => 'Waxing Gibbous',
            self::FULL_MOON, self::NEXT_FULL_MOON => 'Full Moon',
            self::WANING_GIBBOUS => 'Waning Gibbous',
            self::THIRD_QUARTER, self::NEXT_LAST_QUARTER => 'Third Quarter',
            self::WANING_CRESCENT => 'Waning Crescent',
        };
    }
}
