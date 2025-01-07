<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Table
{
    /**
     * MyPDO instance.
     *
     * @var MyPDO
     */
    protected $PDO;

    /**
     * Table name.
     *
     * @var string|InnerQuery
     */
    protected $table_name;

    /**
     * @param string|InnerQuery $table_name Table name
     */
    public function __construct($table_name, MyPDO $PDO)
    {
        $this->table_name = $table_name;
        $this->PDO        = $PDO;
    }

    /**
     * Perform insert query.
     *
     * @return Insert
     */
    public function insert()
    {
        return new Insert($this->table_name, $this->PDO);
    }

    /**
     * Perform replace query.
     *
     * @return Replace
     */
    public function replace()
    {
        return new Replace($this->table_name, $this->PDO);
    }

    /**
     * Perform select query.
     *
     * @param string[] $select_columns Selected column (raw)
     *
     * @return Select
     */
    public function select(array $select_columns = ['*'])
    {
        return new Select($this->table_name, $select_columns, $this->PDO);
    }

    /**
     * Perform update query.
     *
     * @return Update
     */
    public function update()
    {
        return new Update($this->table_name, $this->PDO);
    }

    /**
     * Perform delete query.
     *
     * @return Delete
     */
    public function delete()
    {
        return new Delete($this->table_name, $this->PDO);
    }

    /**
     * Get table information.
     *
     * @return array<string, mixed>
     */
    public function info(): array
    {
        $this->PDO->query(
            'SELECT
                COLUMN_NAME,
                COLUMN_TYPE,
                CHARACTER_SET_NAME,
                COLLATION_NAME,
                IS_NULLABLE,
                ORDINAL_POSITION,
                COLUMN_KEY
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = :dbs AND TABLE_NAME = :table'
        );
        $this->PDO->bind(':table', $this->table_name);
        $this->PDO->bind(':dbs', $this->PDO->configs()['database_name']);

        $result = $this->PDO->resultset();

        return $result === false ? [] : $result;
    }
}
