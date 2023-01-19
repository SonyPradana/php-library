<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 *  @method static \System\Database\MySchema\Create create()
 *  @method static \System\Database\MySchema\Drop drop()
 *  @method static \System\Database\MySchema\Table\Truncate refresh(string $database_name, string $table_name)
 */
final class Schema extends Facade
{
    protected static function getAccessor()
    {
        return 'MySchema';
    }
}
