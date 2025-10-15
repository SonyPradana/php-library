<?php

declare(strict_types=1);

namespace System\Database\MySchema;

use System\Database\Interfaces\Schema;

/** Proxy for drop database and table */
class Drop
{
    public function __construct(
        private Schema\ConnectionInterface $pdo,
        private ?string $database_name = null,
    ) {
    }

    /**
     * Drop database.
     */
    public function database(string $database_name): DB\Drop
    {
        return new DB\Drop($database_name, $this->pdo);
    }

    /**
     * Drop table.
     */
    public function table(string $table_name): Table\Drop
    {
        [$database, $table] = array_pad(explode('.', $table_name, 2), 2, null);
        $database           = $database ?: $this->database_name;
        $table              = $table ?: $table_name;

        return new Table\Drop($database, $table, $this->pdo);
    }
}
