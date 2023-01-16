<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Query;
use System\Database\MySchema\Table\Attributes\DataType;

class Create extends Query
{
    /** @var Column[] */
    private $columns;

    /** @var string[] */
    private $primaryKeys;

    /** @var string */
    private $database_name;

    public function __construct(string $database_name, MyPDO $pdo)
    {
        $this->database_name = $database_name;
        $this->pdo           = $pdo;

        $this->columns     = [];
        $this->primaryKeys = [];

        $this->query         = $this->builder();
    }

    public function __invoke(string $column_name): DataType
    {
        return $this->columns[] = (new Column())->column($column_name);
    }

    public function addColumn(): Column
    {
        return $this->columns[] = new Column();
    }

    /** @param Column[] $columns */
    public function collumns(array $columns): self
    {
        $this->columns = [];
        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    public function primaryKey(string $column_name): self
    {
        $this->primaryKeys[] = $column_name;

        return $this;
    }

    protected function builder(): string
    {
        /** @var string[] */
        $columns = array_merge($this->getColumns(), $this->getPrimarykey());
        $columns = $this->join($columns, ', ');
        $query   = $this->join([$this->database_name, '(', $columns, ')']);

        return $this->query = 'CREATE TABLE ' . $query;
    }

    private function getColumns(): array
    {
        $res = [];

        foreach ($this->columns as $attribute) {
            $res[] = $attribute->__toString();
        }

        return $res;
    }

    private function getPrimarykey(): array
    {
        if (count($this->primaryKeys) === 0) {
            return [''];
        }

        $primaryKeys = implode(', ', $this->primaryKeys);

        return ['PRIMARY KEY (`' . $primaryKeys . '`)'];
    }
}
