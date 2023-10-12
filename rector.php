<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->paths([
        __DIR__,
    ]);
    $rectorConfig->skip([
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
        PreferPHPUnitThisCallRector::class,
    ]);
    $rectorConfig->importNames();
    $rectorConfig->import(LevelSetList::UP_TO_PHP_82);
    $rectorConfig->sets([
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ]);
};
