<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MySchema\DB\Schema;
use System\Database\MySchema\MyPDO;

class MySchema
{
    /** @var MyPDO PDO property */
    private $pdo;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function database()
    {
        return new Schema($this->pdo);
    }
}
