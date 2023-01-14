<?php

declare(strict_types=1);

namespace System\Database\MySchema\DB;

use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Query;
use System\Database\MySchema\Traits\ConditionTrait;

class Drop extends Query
{
    use ConditionTrait;

    /** @var string */
    private $database_name;

    public function __construct(string $database_name, MyPDO $pdo)
    {
        $this->database_name = $database_name;
        $this->pdo           = $pdo;
        $this->query         = $this->builder();
    }

    protected function builder(): string
    {
        $conditon = $this->join([$this->if_exists, $this->database_name]);

        return $this->query = 'DROP DATABASE ' . $conditon . ';';
    }
}
