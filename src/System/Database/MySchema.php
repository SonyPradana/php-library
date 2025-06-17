<?php

declare(strict_types=1);

namespace System\Database;

class MySchema
{
    public function __construct(
        private MySchema\MyPDO $pdo,
        private ?string $database_name = null,
    ) {
        $database_name ??= $this->pdo->getDatabase();
    }

    public function create(): MySchema\Create
    {
        return new MySchema\Create($this->pdo, $this->database_name);
    }

    public function drop(): MySchema\Drop
    {
        return new MySchema\Drop($this->pdo, $this->database_name);
    }

    public function refresh(string $table_name): MySchema\Table\Truncate
    {
        return new MySchema\Table\Truncate($this->database_name, $table_name, $this->pdo);
    }

    /**
     * Create table schema.
     *
     * @param string                                $table_name Target table name
     * @param callable(MySchema\Table\Create): void $blueprint
     */
    public function table(string $table_name, callable $blueprint): MySchema\Table\Create
    {
        $columns = new MySchema\Table\Create($this->database_name, $table_name, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Update table structur.
     *
     * @param string                               $table_name Target table name
     * @param callable(MySchema\Table\Alter): void $blueprint
     */
    public function alter(string $table_name, callable $blueprint): MySchema\Table\Alter
    {
        $columns       = new MySchema\Table\Alter($this->database_name, $table_name, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Run raw table.
     */
    public function raw(string $raw): MySchema\Table\Raw
    {
        return new MySchema\Table\Raw($raw, $this->pdo);
    }
}
