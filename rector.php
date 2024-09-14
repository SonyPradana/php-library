<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/bin',
        __DIR__ . '/src',
    ]);

    $rectorConfig->rule(ChangeSwitchToMatchRector::class);
    $rectorConfig->rule(ClassOnObjectRector::class);
    $rectorConfig->rule(ClosureToArrowFunctionRector::class);
    $rectorConfig->rule(DeclareStrictTypesRector::class);
    $rectorConfig->rule(NullCoalescingOperatorRector::class);
    $rectorConfig->rule(RestoreDefaultNullToNullableTypePropertyRector::class);
    $rectorConfig->rule(TernaryToNullCoalescingRector::class);
};
