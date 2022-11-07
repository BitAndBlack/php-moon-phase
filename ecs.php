<?php

use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
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
    $ECSConfig->import(SetList::PSR_12);
    $ECSConfig->import(SetList::ARRAY);
    $ECSConfig->import(SetList::CLEAN_CODE);
    $ECSConfig->ruleWithConfiguration(YodaStyleFixer::class, [
        'always_move_variable' => true,
    ]);
};
