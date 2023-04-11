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

use Solaris\MoonPhase;

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$moonPhase = new MoonPhase();

$age = round($moonPhase->getAge(), 1);
$stage = $moonPhase->getPhase() < 0.5 ? 'waxing' : 'waning';
$distance = round($moonPhase->getDistance(), 2);
$next = gmdate('G:i:s, j M Y', (int) $moonPhase->getPhaseNextNewMoon());

echo 'The moon is currently ' . $age . ' days old, and is therefore ' . $stage . '. ';
echo 'It is ' . $distance . ' km from the centre of the Earth. ';
echo 'The next new moon is at ' . $next . '. ';