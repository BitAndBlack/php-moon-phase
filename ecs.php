<?php

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ECSConfig): void {
    $ECSConfig->parallel();
    $ECSConfig->paths([
        __DIR__,
    ]);
    $ECSConfig->skip([
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
    ]);
    $ECSConfig->rule(NoUnusedImportsFixer::class);
    $ECSConfig->import(SetList::PSR_12);
};
