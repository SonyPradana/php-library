<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Database\MyQuery\Table table(\System\Database\MyQuery\InnerQuery|string $table_name)
 * @method static \System\Database\MyQuery\Table from(\System\Database\MyQuery\InnerQuery|string $table_name, \System\Database\MyPDO $PDO)
 *
 * @see System\Database\MyQuery
 */
final class DB extends Facade
{
    protected static function getAccessor()
    {
        return 'MyQuery';
    }
}
