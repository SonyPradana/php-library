<?php

declare(strict_types=1);

namespace System\Database\MySchema;

class Create
{
    /** @var MyPDO */
    private $pdo;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function database(string $database_name)
    {
        return new DB\Create($database_name, $this->pdo);
    }

    public function table(string $database_name)
    {
        return new Table\Create($database_name, $this->pdo);
    }
}
