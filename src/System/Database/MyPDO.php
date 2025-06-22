<?php

declare(strict_types=1);

namespace System\Database;

class MyPDO
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
     * @var array{driver: string, host: ?string, database: ?string, port: ?int, chartset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>}
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
            // TODO: retry connection if nessary
            throw $e;
        }
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
     * @return array{driver: string, host: ?string, database: ?string, port: ?int, chartset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>}
     */
    public function configs()
    {
        return $this->configs;
    }

    /**
     * @param array<string, string|int|array<int, int|bool>|null> $configs
     *
     * @return array{driver: string, host: ?string, database: ?string, port: ?int, chartset: ?string, username: ?string, password: ?string, options: array<int, string|int|bool>}
     */
    protected function setConfigs(array $configs): array
    {
        return $this->configs = [
            'driver'   => $configs['driver'] ?? 'mysql',
            'host'     => $configs['host'] ?? null,
            'database' => $configs['database_name'] ?? $configs['database'] ?? null,
            'port'     => $configs['port'] ?? null,
            'chartset' => $configs['chartset'] ?? null,
            'username' => $configs['user'] ?? $configs['username'] ?? null,
            'password' => $configs['password'] ?? null,
            'options'  => $configs['options'] ?? $this->option,
        ];
    }

    /**
     * @param array{host: string, driver: 'mysql'|'mariadb'|'pgsql'|'sqlite', database: ?string, port: ?int, chartset: ?string} $configs
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
     * @throws \InvalidArgumentException
     */
    private function makeMysqlDsn(array $config): string
    {
        // required
        if (false === array_key_exists('host', $config)) {
            throw new \InvalidArgumentException('mysql driver require `host`.');
        }

        $port     = $config['port'] ?? 3306;
        $chartset = $config['chartset'] ?? 'utf8mb4';

        $dsn['host']     = "host={$config['host']}";
        $dsn['dbname']   = isset($config['database']) ? "dbname={$config['database']}" : '';
        $dsn['port']     = "port={$port}";
        $dsn['chartset'] = "chartset={$chartset}";
        $build           = implode(';', array_filter($dsn, fn (string $item): bool => '' !== $item));

        return "mysql:{$build}";
    }

    /**
     * @param array<string, string|int|array<int, string|bool>> $config
     *
     * @throws \InvalidArgumentException
     */
    private function makePgsqlDsn(array $config): string
    {
        // required
        if (false === array_key_exists('host', $config)) {
            throw new \InvalidArgumentException('pgsql driver require `host` and `dbname`.');
        }

        $port     = $config['port'] ?? 5432;
        $chartset = $config['chartset'] ?? 'utf8';

        $dsn['host']     = "host={$config['host']}";
        $dsn['dbname']   = isset($config['database']) ? "dbname={$config['database']}" : '';
        $dsn['port']     = "port={$port}";
        $dsn['encoding'] = "client_encoding={$chartset}";
        $build           = implode(';', array_filter($dsn, fn (string $item): bool => '' !== $item));

        return "pgsql:{$build}";
    }

    /**
     * @param array<string, string|int|array<int, string|bool>> $config
     *
     * @throws \InvalidArgumentException
     */
    private function makeSqliteDsn(array $config): string
    {
        if (false === array_key_exists('database', $config)) {
            throw new \InvalidArgumentException('sqlite driver require `database`.');
        }
        $path = $config['database'];

        if ($path === ':memory:'
            || str_contains($path, '?mode=memory')
            || str_contains($path, '&mode=memory')
        ) {
            return "sqlite:{$path}";
        }

        if (false === ($path = realpath($path))) {
            throw new \InvalidArgumentException('sqlite driver require `database` with absolute path.');
        }

        return "sqlite:{$path}";
    }

    /**
     *  mempersiapkan statement pada query.
     */
    public function query(string $query): self
    {
        $this->stmt = $this->dbh->prepare($this->query = $query);

        return $this;
    }

    /**
     * Menggantikan paramater input dari user dengan sebuah placeholder.
     *
     * @param int|string|bool|null $param
     * @param mixed                $value
     * @param int|string|bool|null $type
     */
    public function bind($param, $value, $type = null): self
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
     * Menjalankan atau mengeksekusi query.
     *
     * @return bool True if success
     *
     * @throws \PDOException
     */
    public function execute()
    {
        $start    = microtime(true);
        $execute  = $this->stmt->execute();
        $elapsed  = round((microtime(true) - $start) * 1000, 2);

        $this->addLog($this->query, $elapsed);

        return $execute;
    }

    /**
     * mengembalikan hasil dari query yang dijalankan berupa array.
     *
     * @return mixed[]|false
     */
    public function resultset()
    {
        $this->execute();

        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mengembalikan hasil dari query, ditampilkan hanya satu baris data saja.
     *
     * @return mixed
     */
    public function single()
    {
        $this->execute();

        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * menampilkan jumlah data yang berhasil di simpan, di ubah maupun dihapus.
     *
     * @return int the number of rows
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * id dari data yang terakhir disimpan.
     *
     * @return string|false last id
     */
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * @return bool Transaction status
     */
    public function transaction(callable $callable)
    {
        if (false === $this->beginTransaction()) {
            return false;
        }

        $return_call =  call_user_func($callable);
        if (false === $return_call) {
            return $this->cancelTransaction();
        }

        return $this->endTransaction();
    }

    /**
     * Initiates a transaction.
     *
     * @return bool True if success
     *
     * @throws \PDOException
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool True if success
     *
     * @throws \PDOException
     */
    public function endTransaction(): bool
    {
        return $this->dbh->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool True if success
     *
     * @throws \PDOException
     */
    public function cancelTransaction(): bool
    {
        return $this->dbh->rollBack();
    }

    protected function addLog(string $query, float $elapsed_time): void
    {
        $this->logs[] = [
            'query' => $query,
            'time'  => $elapsed_time,
        ];
    }

    /**
     * Flush logs query.
     */
    public function flushLogs(): void
    {
        $this->logs = [];
    }

    /**
     * Get logs query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
