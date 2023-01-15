<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 *  @method static \System\Database\MySchema\Create create()
 *  @method static \System\Database\MySchema\Drop drop()
 */
final class Schema extends Facade
{
    protected static function getAccessor()
    {
        return 'MySchema';
    }
}
