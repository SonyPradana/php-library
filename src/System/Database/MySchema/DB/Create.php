<?php

declare(strict_types=1);

namespace System\Database\MySchema\DB;

use System\Database\Interfaces\Schema;
use System\Database\MySchema\Query;
use System\Database\MySchema\Traits\ConditionTrait;

class Create extends Query
{
    use ConditionTrait;

    /** @var string */
    private $database_name;

    public function __construct(string $database_name, Schema\ConnectionInterface $pdo)
    {
        $this->database_name = $database_name;
        $this->pdo           = $pdo;
    }

    protected function builder(): string
    {
        $conditon = $this->join([$this->if_exists, $this->database_name]);

        return 'CREATE DATABASE ' . $conditon . ';';
    }
}
