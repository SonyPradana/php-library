<?php

declare(strict_types=1);

namespace System\Database;

class MyPDO
{
    /** @var \PDO PDO */
    private $dbh;
    /** @var \PDOStatement */
    private $stmt;

    /**
     * Connection configuration.
     *
     * @var array<string, string>
     */
    protected $configs;

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
     * @param array<string, string> $configs
     */
    public function __construct(array $configs)
    {
        $database_name    = $configs['database_name'];
        $host             = $configs['host'];
        $user             = $configs['user'];
        $pass             = $configs['password'];

        $this->configs = $configs;
        $dsn           = "mysql:host=$host;dbname=$database_name";
        $this->useDsn($dsn, $user, $pass);
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
     * @return array<string, string>
     */
    public function configs()
    {
        return $this->configs;
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
