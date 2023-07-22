<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MySchema\Create;
use System\Database\MySchema\Drop;
use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Table\Alter;
use System\Database\MySchema\Table\Create as CreateTable;
use System\Database\MySchema\Table\Raw;
use System\Database\MySchema\Table\Truncate;

class MySchema
{
    /** @var MyPDO PDO property */
    private $pdo;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(): Create
    {
        return new Create($this->pdo);
    }

    public function drop(): Drop
    {
        return new Drop($this->pdo);
    }

    public function refresh(string $table_name): Truncate
    {
        $database_name = $this->pdo->configs()['database_name'];

        return new Truncate($database_name, $table_name, $this->pdo);
    }

    public function table(string $table_name, callable $blueprint): CreateTable
    {
        $database_name = $this->pdo->configs()['database_name'];
        $columns       = new CreateTable($database_name, $table_name, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Update table structur.
     *
     * @param string                $table_name Target table name
     * @param callable(Alter): void $blueprint
     */
    public function alter(string $table_name, callable $blueprint): Alter
    {
        $database_name = $this->pdo->configs()['database_name'];
        $columns       = new Alter($database_name, $table_name, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Run raw table.
     */
    public function raw(string $raw): Raw
    {
        return new Raw($raw, $this->pdo);
    }
}
