<?php

declare(strict_types=1);

namespace System\Database\MySchema;

/** Proxy for create database and table */
class Create
{
    public function __construct(
        private MyPDO $pdo,
        private ?string $database_name = null,
    ) {
    }

    /**
     * Create database.
     */
    public function database(string $database_name): DB\Create
    {
        return new DB\Create($database_name, $this->pdo);
    }

    /**
     * Create table.
     */
    public function table(string $table_name): Table\Create
    {
        return new Table\Create($this->database_name, $table_name, $this->pdo);
    }
}
