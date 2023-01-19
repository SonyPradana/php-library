<?php

declare(strict_types=1);

namespace System\Database\MySchema;

/** Proxy for drop database and table */
class Drop
{
    /** @var MyPDO */
    private $pdo;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function database(string $database_name): DB\Drop
    {
        return new DB\Drop($database_name, $this->pdo);
    }

    public function table(string $table_name): Table\Drop
    {
        $database_name = $this->pdo->configs()['database_name'];

        return new Table\Drop($database_name, $table_name, $this->pdo);
    }
}
