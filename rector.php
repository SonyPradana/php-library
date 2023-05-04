<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\CodingStyle\Rector\Property\NullifyUnionNullableRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    // register a single rule
    $rectorConfig->rule(AddArrayDefaultToArrayPropertyRector::class);
    $rectorConfig->rule(AddFalseDefaultToBoolPropertyRector::class);
    $rectorConfig->rule(NullifyUnionNullableRector::class);
};
