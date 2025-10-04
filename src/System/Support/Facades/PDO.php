<?php

declare(strict_types=1);

namespace System\Support\Facades;

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
final class PDO extends Facade
{
    protected static function getAccessor()
    {
        return \System\Database\DatabaseManager::class;
    }

    public static function instance(): \System\Database\DatabaseManager
    {
        return self::getFacade();
    }
}
