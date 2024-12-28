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
    public const ORDER_ASC   = 0;
    public const ORDER_DESC  = 1;
    /** @var MyPDO */
    protected $PDO;

    /**
     * Create new Builder.
     *
     * @param MyPDO $PDO the PDO connection
     */
    public function __construct(MyPDO $PDO)
    {
        $this->PDO = $PDO;
    }

    /**
     * Create builder using invoke.
     *
     * @param string $table_name Table name
     *
     * @return Table
     */
    public function __invoke(string $table_name)
    {
        return $this->table($table_name);
    }

    /**
     * Create builder and set table name.
     *
     * @param string|InnerQuery $table_name Table name
     *
     * @return Table
     */
    public function table($table_name)
    {
        return new Table($table_name, $this->PDO);
    }

    /**
     * Create Builder using static function.
     *
     * @param string|InnerQuery $table_name Table name
     * @param MyPDO             $PDO        The PDO connection, null give global instance
     *
     * @return Table
     */
    public static function from($table_name, MyPDO $PDO)
    {
        $conn = new MyQuery($PDO);

        return $conn->table($table_name);
    }
}
