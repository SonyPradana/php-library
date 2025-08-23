<?php

declare(strict_types=1);

namespace System\Test\Database;

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;
use System\Database\MyQuery\Insert;
use System\Database\MySchema;

abstract class TestDatabase extends TestCase
{
    /** @var array<string, string|int> */
    protected array $env;
    protected MyPDO $pdo;
    protected MySchema\MyPDO $pdo_schema;
    protected MySchema $schema;

    protected function createConnection(): void
    {
        $this->setupEnv($_ENV['DB_CONNECTION'] ?? 'mysql');
        $this->pdo_schema = new MySchema\MyPDO($this->env);
        $this->schema     = new MySchema($this->pdo_schema, $this->env['database']);

        // building the database
        $this->schema->create()->database($this->env['database'])->ifNotExists()->execute();

        $this->pdo = new MyPDO($this->env);
    }

    protected function dropConnection(): void
    {
        $this->schema->drop()->database($this->env['database'])->ifExists()->execute();
    }

    protected function createUserSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE users (
                user      varchar(32)  NOT NULL,
                password  varchar(500) NOT NULL,
                stat      int(2)       NOT NULL,
                PRIMARY KEY (user)
            )')
           ->execute();
    }

    protected function setupEnv(string $use_connection = 'mysql'): void
    {
        $this->env = match ($use_connection) {
            'mysql', 'mariadb' => [
                'driver'   => 'mysql',
                'host'     => '127.0.0.1',
                'username' => 'root',
                'password' => '',
                'database' => 'testing_db',
                'port'     => 3306,
                'chartset' => 'utf8mb4',
            ],
        };
    }

    /**
     * Insert new Row of user.
     *
     * @param array<int, array<string, string|int|bool|null>> $users Format [{user, password, stat}]
     */
    protected function createUser($users): bool
    {
        return (new Insert('users', $this->pdo))
            ->rows($users)
            ->execute();
    }
}
