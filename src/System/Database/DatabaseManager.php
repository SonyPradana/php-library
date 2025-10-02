<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\Interfaces\ConnectionInterface;

class DatabaseManager implements ConnectionInterface
{
    private ConnectionInterface $connection;

    /** @var array<string, ConnectionInterface> */
    private array $connections = [];

    /**
     * @param array<string, array<string, string|int|array<int, string|int|bool>|null>> $configs
     */
    public function __construct(private array $configs)
    {
    }

    public function clearConnections(): void
    {
        $this->connections = [];
    }

    public function connection(string $name): ConnectionInterface
    {
        if (false === isset($this->connections[$name])) {
            if (false === isset($this->configs[$name])) {
                throw new \InvalidArgumentException("Database connection [{$name}] not configured.");
            }

            $config = $this->configs[$name];

            $this->connections[$name] = new MyPDO($config);
        }

        return $this->connections[$name];
    }

    public function setDefaultConnection(ConnectionInterface $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $query): self
    {
        $this->connection->query($query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function bind(int|string|bool|null $param, mixed $value, int|string|bool|null $type = null): self
    {
        $this->connection->bind($param, $value, $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        return $this->connection->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function resultset(): array|false
    {
        return $this->connection->resultset();
    }

    /**
     * {@inheritdoc}
     */
    public function single(): mixed
    {
        return $this->connection->single();
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount(): int
    {
        return $this->connection->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId(): string|false
    {
        return $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function flushLogs(): void
    {
        $this->connection->flushLogs();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogs(): array
    {
        return $this->connection->getLogs();
    }
}
