<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Database\MyQuery\Table table(string $from)
 */
final class DB extends Facade
{
    protected static function getAccessor()
    {
        return 'MyQuery';
    }
}
