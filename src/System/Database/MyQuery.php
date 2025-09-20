<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MyQuery\InnerQuery;
use System\Database\MyQuery\Table;

/**
 * Query Builder.
 */
class MyQuery
{
    public const ORDER_ASC  = 0;
    public const ORDER_DESC = 1;

    /**
     * Create new Builder.
     *
     * @param MyPDO $PDO the PDO connection
     */
    public function __construct(protected MyPDO $PDO)
    {
    }

    /**
     * Create builder using invoke.
     */
    public function __invoke(string $table_name): Table
    {
        return $this->table($table_name);
    }

    /**
     * Create builder and set table name.
     */
    public function table(string|InnerQuery $table_name): Table
    {
        return new Table($table_name, $this->PDO);
    }

    /**
     * Create Builder using static function.
     */
    public static function from(string|InnerQuery $table_name, MyPDO $PDO): Table
    {
        $conn = new MyQuery($PDO);

        return $conn->table($table_name);
    }
}
