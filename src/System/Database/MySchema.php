<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MySchema\Create;
use System\Database\MySchema\Drop;
use System\Database\MySchema\MyPDO;

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
}
