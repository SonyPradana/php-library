<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Query;
use System\Database\MySchema\Table\Attributes\DataType;

class Create extends Query
{
    public const INNODB    = 'INNODB';
    public const MYISAM    = 'MYISAM';
    public const MEMORY    = 'MEMORY';
    public const MERGE     = 'MERGE';
    public const EXAMPLE   = 'EXAMPLE';
    public const ARCHIVE   = 'ARCHIVE';
    public const CSV       = 'CSV';
    public const BLACKHOLE = 'BLACKHOLE';
    public const FEDERATED = 'FEDERATED';

    /** @var Column[]|DataType[] */
    private $columns;

    /** @var string[] */
    private $primaryKeys;

    /** @var string[] */
    private $uniques;

    /** @var string */
    private $store_engine;

    private string $character_set;

    /** @var string */
    private $table_name;

    public function __construct(string $database_name, string $table_name, MyPDO $pdo)
    {
        $this->table_name    = $database_name . '.' . $table_name;
        $this->pdo           = $pdo;
        $this->columns       = [];
        $this->primaryKeys   = [];
        $this->uniques       = [];
        $this->store_engine  = '';
        $this->character_set = '';
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

    public function unique(string $unique): self
    {
        $this->uniques[] = $unique;

        return $this;
    }

    public function engine(string $engine): self
    {
        $this->store_engine = $engine;

        return $this;
    }

    public function character(string $character_set): self
    {
        $this->character_set = $character_set;

        return $this;
    }

    protected function builder(): string
    {
        /** @var string[] */
        $columns = array_merge($this->getColumns(), $this->getPrimarykey(), $this->getUnique());
        $columns = $this->join($columns, ', ');
        $query   = $this->join([$this->table_name, '(', $columns, ')' . $this->getStoreEngine() . $this->getCharacterSet()]);

        return 'CREATE TABLE ' . $query;
    }

    /** @return string[] */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->columns as $attribute) {
            $res[] = $attribute->__toString();
        }

        return $res;
    }

    /** @return string[] */
    private function getPrimarykey(): array
    {
        if (count($this->primaryKeys) === 0) {
            return [''];
        }

        $primaryKeys = array_map(fn ($primaryKey) => $primaryKey, $this->primaryKeys);
        $primaryKeys = implode(', ', $primaryKeys);

        return ["PRIMARY KEY ({$primaryKeys})"];
    }

    /** @return string[] */
    private function getUnique(): array
    {
        if (count($this->uniques) === 0) {
            return [''];
        }

        $uniques = implode(', ', $this->uniques);

        return ["UNIQUE ({$uniques})"];
    }

    private function getStoreEngine(): string
    {
        return $this->store_engine === '' ? '' : ' ENGINE=' . $this->store_engine;
    }

    private function getCharacterSet(): string
    {
        return $this->character_set === '' ? '' : " CHARACTER SET {$this->character_set}";
    }
}
