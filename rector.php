<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->paths([
        __DIR__,
    ]);
    $rectorConfig->skip([
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
    ]);
    $rectorConfig->importNames();
    $rectorConfig->import(LevelSetList::UP_TO_PHP_72);
};
