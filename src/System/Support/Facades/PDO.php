<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static self                                                                                                                                                               instance()
 * @method static MyPDO                                                                                                                                                              conn(array<string, string> $configs)
 * @method static array{driver: string, host: ?string, database: ?string, port: ?int, chartset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>} configs()
 * @method static string                                                                                                                                                             getDsn(array{host: string, driver: 'mysql'|'mariadb'|'pgsql'|'sqlite', database: ?string, port: ?int, chartset: ?string} $configs)
 * @method static self                                                                                                                                                               query(string $query)
 * @method static self                                                                                                                                                               bind(int|string|bool|null $param, mixed $value, int|string|bool|null $type = null)
 * @method static bool                                                                                                                                                               execute()
 * @method static mixed[]|false                                                                                                                                                      resultset()
 * @method static mixed                                                                                                                                                              single()
 * @method static int                                                                                                                                                                rowCount()
 * @method static string|false                                                                                                                                                       lastInsertId()
 * @method static bool                                                                                                                                                               transaction(callable $callable)
 * @method static bool                                                                                                                                                               beginTransaction()
 * @method static bool                                                                                                                                                               endTransaction()
 * @method static bool                                                                                                                                                               cancelTransaction()
 * @method static void                                                                                                                                                               flushLogs()
 * @method static array<int, array<string, mixed>>                                                                                                                                   getLogs()
 *
 * @see System\Database\MyPDO
 */
final class PDO extends Facade
{
    protected static function getAccessor()
    {
        return \System\Database\MyPDO::class;
    }
}
