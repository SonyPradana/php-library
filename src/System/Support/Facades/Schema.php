<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 *  @method static \System\Database\MySchema\DB\Schema database()
 */
final class Schema extends Facade
{
    protected static function getAccessor()
    {
        return 'MySchema';
    }
}
