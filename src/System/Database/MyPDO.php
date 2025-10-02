<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\Exceptions\InvalidConfigurationException;
use System\Database\Interfaces\ConnectionInterface;

class MyPDO implements ConnectionInterface
{
    protected \PDO $dbh;
    private \PDOStatement $stmt;

    /** @var array<int, string|int|bool> */
    protected array $option = [
        \PDO::ATTR_PERSISTENT => true,
        \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
    ];

    /**
     * Connection configuration.
     *
     * @var array{driver: string, host: ?string, database: ?string, port: ?int, charset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>}
     */
    protected array $configs;

    /**
     * Query prepare statment;.
     */
    protected string $query;

    /**
     * Log query when execute and fatching.
     * - query.
     *
     * @var array<int, array<string, mixed>>
     */
    protected $logs = [];

    /**
     * @param array<string, string|int|array<int, string|int|bool>|null> $configs
     */
    public function __construct(array $configs)
    {
        $dsn_config = $this->setConfigs($configs);
        $dsn        = $this->getDsn($dsn_config);
        $this->dbh  = $this->createConnection($dsn, $dsn_config, $dsn_config['options']);
    }

    /**
     * Singleton pattern implemnt for Databese connation.
     *
     * @return self
     */
    public function instance()
    {
        return $this;
    }

    /**
     * @deprecated use createConnection instead
     *
     * @throws \Exception
     */
    protected function useDsn(string $dsn, string $user, string $pass): self
    {
        $option = [
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE    => \PDO::ERRMODE_EXCEPTION,
        ];

        try {
            $this->dbh = new \PDO($dsn, $user, $pass, $option);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param array<string, string>       $configs
     * @param array<int, string|int|bool> $options
     *
     * @throws \PDOException
     */
    protected function createConnection(string $dsn, array $configs, array $options): \PDO
    {
        [$username, $password] = [
            $configs['username'] ?? null, $configs['password'] ?? null,
        ];

        try {
            return new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            if (true === $this->causedByLostConnection($e)) {
                return new \PDO($dsn, $username, $password, $options);
            }

            throw $e;
        }
    }

    /**
     * This code ispired by Laravel Framework.
     *
     * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Database/DetectsLostConnections.php
     */
    protected function causedByLostConnection(\Throwable $e): bool
    {
        $errors = [
            // MySQL/MariaDB
            'child connection forced to terminate due to client_idle_limit',
            'SQLSTATE[HY000] [2002] Operation in progress',
            'Error writing data to the connection',
            'running with the --read-only option',
            'Server is in script upgrade mode',
            'Packets out of order. Expected',
            'Resource deadlock avoided',
            'is dead or not enabled',
            'server has gone away',
            'Error while sending',
            'query_wait_timeout',
            'Lost connection',
            // PostgreSQL
            'could not connect to server: Connection refused',
            'server closed the connection unexpectedly',
            'connection is no longer usable',
            'no connection to the server',
            // SQLite
            'No such file or directory',
            'Transaction() on null',
            // SSL
            'SQLSTATE[HY000]: General error: 7 SSL SYSCALL error',
            'SSL connection has been closed unexpectedly',
            'decryption failed or bad record mac',
            'SSL: Connection timed out',
            'SSL: Operation timed out',
            'SSL: Broken pipe',
            // Network error
            'The connection is broken and recovery is not possible',
            'Physical connection is not usable',
            'Communication link failure',
            'No route to host',
            'reset by peer',
            // Network timeout
            'Connection timed out',
            'Login timeout expired',
            // General error
            'SQLSTATE[HY000] [2002] Connection refused',
            'SQLSTATE[08S01]: Communication link failure',
            'php_network_getaddresses: getaddrinfo failed',
            'The client was disconnected by the server because of inactivity',
            'Temporary failure in name resolution',
            'could not translate host name',
        ];

        $message = $e->getMessage();

        foreach ($errors as $error) {
            if (false !== stripos($message, $error)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create connaction using static.
     *
     * @param array<string, string> $configs
     *
     * @return MyPDO
     * */
    public static function conn(array $configs)
    {
        return new self($configs);
    }

    /**
     * Get connection configuration.
     *
     * @return array{driver: string, host: ?string, database: ?string, port: ?int, charset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>}
     */
    public function configs()
    {
        return $this->configs;
    }

    /**
     * @param array<string, string|int|array<int, int|bool>|null> $configs
     *
     * @return array{driver: string, host: ?string, database: ?string, port: ?int, charset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>}
     */
    protected function setConfigs(array $configs): array
    {
        return $this->configs = [
            'driver'   => $configs['driver'] ?? 'mysql',
            'host'     => $configs['host'] ?? null,
            'database' => $configs['database_name'] ?? $configs['database'] ?? null,
            'port'     => $configs['port'] ?? null,
            'charset'  => $configs['charset'] ?? null,
            'username' => $configs['user'] ?? $configs['username'] ?? null,
            'password' => $configs['password'] ?? null,
            'options'  => $configs['options'] ?? $this->option,
        ];
    }

    /**
     * @param array{host: string, driver: 'mysql'|'mariadb'|'pgsql'|'sqlite', database: ?string, port: ?int, charset: ?string} $configs
     */
    public function getDsn(array $configs): string
    {
        return match ($configs['driver']) {
            'mysql', 'mariadb' => $this->makeMysqlDsn($configs),
            'pgsql'  => $this->makePgsqlDsn($configs),
            'sqlite' => $this->makeSqliteDsn($configs),
        };
    }

    /**
     * @param array<string, string|int|array<int, string|bool>> $config
     *
     * @throws InvalidConfigurationException
     */
    private function makeMysqlDsn(array $config): string
    {
        // required
        if (false === array_key_exists('host', $config)) {
            throw new InvalidConfigurationException('mysql driver require `host`.');
        }
        $dsn['host']    = "host={$config['host']}";
        $dsn['dbname']  = isset($config['database']) ? "dbname={$config['database']}" : '';
        $dsn['port']    = 'port=' . ($config['port'] ?? 3306);
        $dsn['charset'] = 'charset=' . ($config['charset'] ?? 'utf8mb4');

        $build = implode(';', array_filter($dsn, fn (string $item): bool => '' !== $item));

        return "mysql:{$build}";
    }

    /**
     * @param array<string, string|int|array<int, string|bool>> $config
     *
     * @throws InvalidConfigurationException
     */
    private function makePgsqlDsn(array $config): string
    {
        // required
        if (false === array_key_exists('host', $config)) {
            throw new InvalidConfigurationException('pgsql driver require `host`.');
        }

        $dsn['host']     = "host={$config['host']}";
        $dsn['dbname']   = isset($config['database']) ? "dbname={$config['database']}" : '';
        $dsn['port']     = 'port=' . ($config['port'] ?? 5432);
        $dsn['encoding'] = 'client_encoding=' . ($config['charset'] ?? 'utf8');

        $build = implode(';', array_filter($dsn, fn (string $item): bool => '' !== $item));

        return "pgsql:{$build}";
    }

    /**
     * @param array<string, string|int|array<int, string|bool>> $config
     *
     * @throws InvalidConfigurationException
     */
    private function makeSqliteDsn(array $config): string
    {
        if (false === array_key_exists('database', $config)) {
            throw new InvalidConfigurationException('sqlite driver require `database`.');
        }
        $path = $config['database'];

        if ($path === ':memory:'
            || str_contains($path, '?mode=memory')
            || str_contains($path, '&mode=memory')
        ) {
            return "sqlite:{$path}";
        }

        if (false === ($path = realpath($path))) {
            throw new InvalidConfigurationException('sqlite driver require `database` with absolute path.');
        }

        return "sqlite:{$path}";
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $query): self
    {
        $this->stmt = $this->dbh->prepare($this->query = $query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function bind(int|string|bool|null $param, mixed $value, int|string|bool|null $type = null): self
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value)  => \PDO::PARAM_INT,
                is_bool($value) => \PDO::PARAM_BOOL,
                is_null($value) => \PDO::PARAM_NULL,
                default         => \PDO::PARAM_STR,
            };
        }
        $this->stmt->bindValue($param, $value, $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $start    = microtime(true);
        $execute  = $this->stmt->execute();
        $this->addLog($this->query, $start, microtime(true));

        return $execute;
    }

    /**
     * {@inheritdoc}
     */
    public function resultset(): array|false
    {
        $this->execute();

        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function single(): mixed
    {
        $this->execute();

        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId(): string|false
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function transaction(callable $callable): bool
    {
        try {
            if (false === $this->beginTransaction()) {
                return false;
            }

            $return_call =  call_user_func($callable, $this);
            if (true !== $return_call) {
                $this->cancelTransaction();

                return false;
            }

            return $this->endTransaction();
        } catch (\Throwable $th) {
            $this->cancelTransaction();

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function endTransaction(): bool
    {
        return $this->dbh->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function cancelTransaction(): bool
    {
        return $this->dbh->rollBack();
    }

    protected function addLog(string $query, float $start_time, float $end_time): void
    {
        $this->logs[] = [
            'query'    => $query,
            'started'  => $start_time,
            'ended'    => $end_time,
            'duration' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function flushLogs(): void
    {
        $this->logs = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLogs(): array
    {
        foreach ($this->logs as &$log) {
            $log['duration'] ??= round(($log['ended'] - $log['started']) * 1000, 2);
        }
        unset($log);

        return $this->logs;
    }
}
