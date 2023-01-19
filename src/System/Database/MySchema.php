<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MySchema\Create;
use System\Database\MySchema\Drop;
use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Table\Truncate;

class MySchema
{
    /** @var MyPDO PDO property */
    private $pdo;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create()
    {
        return new Create($this->pdo);
    }

    public function drop()
    {
        return new Drop($this->pdo);
    }

    public function refresh(string $table_name)
    {
        $database_name = $this->pdo->configs()['database_name'];

        return new Truncate($database_name, $table_name, $this->pdo);
    }
}
