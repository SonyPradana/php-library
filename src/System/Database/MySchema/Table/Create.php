<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Query;

class Create extends Query
{
    /** @var Column[] */
    private $columns;

    /** @var string */
    private $primaryKeys;

    /** @var string */
    private $database_name;

    public function __construct(string $database_name, MyPDO $pdo)
    {
        $this->database_name = $database_name;
        $this->pdo           = $pdo;

        $this->columns     = [];
        $this->primaryKeys = '';

        $this->query         = $this->builder();
    }

    public function addColumn(): Column
    {
        return $this->columns[] = new Column();
    }

    public function primaryKey(string $column_name): self
    {
        $this->primaryKeys = 'PRIMARY KEY (`' . $column_name . '`)';

        return $this;
    }

    protected function builder(): string
    {
        /** @var string[] */
        $columns = array_merge($this->columns, [$this->primaryKeys]);
        $columns = implode(', ', $columns);
        $query   = $this->join([$this->database_name, '(', $columns, ')']);

        return $this->query = 'CREATE TABLE ' . $query;
    }
}
