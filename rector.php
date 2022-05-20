<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->paths([
        __DIR__.DIRECTORY_SEPARATOR.'src',
        __DIR__.DIRECTORY_SEPARATOR.'tests',
    ]);
    $rectorConfig->importNames();
    $rectorConfig->import(LevelSetList::UP_TO_PHP_72);
};
