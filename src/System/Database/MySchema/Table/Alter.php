<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table;

use System\Database\MySchema\MyPDO;
use System\Database\MySchema\Query;
use System\Database\MySchema\Table\Attributes\DataType;

class Alter extends Query
{
    /** @var Column[]|DataType[] */
    private $alter_columns = [];

    /** @var Column[]|DataType[] */
    private $add_columns = [];

    /** @var string[] */
    private $drop_columns = [];

    /** @var string */
    private $table_name;

    public function __construct(string $database_name, string $table_name, MyPDO $pdo)
    {
        $this->table_name   = $database_name . '.' . $table_name;
        $this->pdo          = $pdo;
    }

    /**
     * Add new column to the exist table.
     */
    public function __invoke(string $column_name): DataType
    {
        return $this->alter_columns[] = (new Column())->column($column_name);
    }

    public function add(string $column_name): DataType
    {
        return $this->add_columns[] = (new Column())->column($column_name);
    }

    public function drop(string $column_name)
    {
        return $this->drop_columns[] = $column_name;
    }

    public function column(string $column_name): DataType
    {
        return $this->alter_columns[] = (new Column())->column($column_name);
    }

    protected function builder(): string
    {
        $query = [];

        // alter
        $query = array_merge($query, $this->getModify());

        // add
        $query = array_merge($query, $this->getColumns());

        // drop
        $drops = array_map(fn ($column) => "DROP COLUMN `{$column}`;", $this->drop_columns);
        $query = array_merge($query, $drops);

        $query = implode(' ', $query);

        return "ALTER TABLE {$this->table_name} {$query}";
    }

    /** @return string[] */
    private function getModify(): array
    {
        $res = [];

        foreach ($this->alter_columns as $attribute) {
            $res[] = "MODIFY COLUMN {$attribute->__toString()};";
        }

        return $res;
    }

    /** @return string[] */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->add_columns as $attribute) {
            $res[] = "ADD {$attribute->__toString()};";
        }

        return $res;
    }
}
