<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Database\MySchema\Create         create()
 * @method static \System\Database\MySchema\Drop           drop()
 * @method static \System\Database\MySchema\Table\Truncate refresh(string $table_name)
 * @method static \System\Database\MySchema\Table\Create   table(string $table_name, callable $blueprint)
 * @method static \System\Database\MySchema\Table\Alter    alter(string $table_name, callable $blueprint)
 * @method static \System\Database\MySchema\Table\Raw      raw(string $raw)
 */
final class Schema extends Facade
{
    protected static function getAccessor()
    {
        return 'MySchema';
    }
}
