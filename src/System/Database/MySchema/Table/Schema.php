<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

use System\Database\MySchema\MyPDO;

class Schema
{
    /** @var MyPDO PDO property */
    private $pdo;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $database_name)
    {
        return new Create($database_name, $this->pdo);
    }
}
