<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Query;
use System\Database\MySchema\Traits\ConditionTrait;

class Truncate extends Query
{
    use ConditionTrait;

    /** @var string */
    private $table_name;

    public function __construct(string $database_name, string $table_name, MyPDO $pdo)
    {
        $this->table_name    = $database_name . '.' . $table_name;
        $this->pdo           = $pdo;
    }

    protected function builder(): string
    {
        $conditon = $this->join([$this->if_exists, $this->table_name]);

        return 'TRUNCATE TABLE ' . $conditon . ';';
    }
}
