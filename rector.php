<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\CodingStyle\Rector\Property\NullifyUnionNullableRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    // register a single rules
    $rectorConfig->rules([
        AddArrayDefaultToArrayPropertyRector::class,
        AddFalseDefaultToBoolPropertyRector::class,
        NullifyUnionNullableRector::class,
        ReturnEarlyIfVariableRector::class,
        RemoveEmptyClassMethodRector::class,
        RemoveUnreachableStatementRector::class,
        RemoveUnusedForeachKeyRector::class,
    ]);
};
