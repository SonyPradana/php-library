<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MyQuery\Table;

/**
 * Query Builder.
 */
class MyQuery
{
    public const ORDER_ASC   = 0;
    public const ORDER_DESC  = 1;
    /** @var MyPDO */
    protected $PDO    = null;

    /**
     * Create new Builder.
     *
     * @param MyPDO $PDO The PDO connection, null give global instance
     */
    public function __construct(MyPDO $PDO = null)
    {
        $this->PDO = $PDO ?? MyPDO::getInstance();
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
     * @param string $table_name Table name
     *
     * @return Table
     */
    public function table(string $table_name)
    {
        return new Table($table_name, $this->PDO);
    }

    /**
     * Create Builder using static function.
     *
     * @param string $table_name Table name
     * @param MyPDO  $PDO        The PDO connection, null give global instance
     *
     * @return Table
     */
    public static function from(string $table_name, MyPDO $PDO = null)
    {
        $conn = new MyQuery($PDO);

        return $conn->table($table_name);
    }
}
