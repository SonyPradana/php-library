<?php

declare(strict_types=1);

namespace System\Database\Seeder;

use System\Database\Interfaces\ConnectionInterface;
use System\Database\MyQuery\Insert;

abstract class Seeder
{
    protected ConnectionInterface $pdo;

    public function __construct(ConnectionInterface $pdo)
    {
        $this->pdo =  $pdo;
    }

    /**
     * @param class-string $class_name
     */
    public function call(string $class_name): void
    {
        $class = new $class_name($this->pdo);
        $class->run();
    }

    public function create(string $table_name): Insert
    {
        return new Insert($table_name, $this->pdo);
    }

    /**
     * Run seeder.
     */
    abstract public function run(): void;
}
