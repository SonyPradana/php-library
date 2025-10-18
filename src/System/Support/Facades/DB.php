<?php

declare(strict_types=1);

namespace System\Support\Facades;

use System\Database\MyPDO;
use System\Database\MyQuery\InnerQuery;
use System\Database\MyQuery\Table;

/**
 * @method static void                                            clearConnections()
 * @method static \System\Database\Interfaces\ConnectionInterface connection(string $name)
 * @method static \System\Database\DatabaseManager                setDefaultConnection(\System\Database\Interfaces\ConnectionInterface $connection)
 * @method static \System\Database\DatabaseManager                query(string $query)
 * @method static \System\Database\DatabaseManager                bind(string|int|bool|null $param, mixed $value, string|int|bool|null $type = null)
 * @method static bool                                            execute()
 * @method static mixed[]|false                                   resultset()
 * @method static mixed                                           single()
 * @method static int                                             rowCount()
 * @method static bool                                            transaction(callable $callable)
 * @method static bool                                            beginTransaction()
 * @method static bool                                            endTransaction()
 * @method static bool                                            cancelTransaction()
 * @method static string|false                                    lastInsertId()
 * @method static void                                            flushLogs()
 * @method static array<int, array<string, float|string|null>>    getLogs()
 *
 * @see System\Database\DatabaseManager
 */
final class DB extends Facade
{
    protected static function getAccessor()
    {
        return \System\Database\DatabaseManager::class;
    }

    /**
     * Create builder and set table name.
     *
     * @deprecated since v0.40.3
     */
    public static function table(string|InnerQuery $table_name): Table
    {
        return new Table($table_name, PDO::instance());
    }

    /**
     * Create Builder using static function.
     *
     * @deprecated since v0.40.3
     */
    public static function from(string|InnerQuery $table_name, MyPDO $pdo): Table
    {
        return new Table($table_name, $pdo);
    }
}
