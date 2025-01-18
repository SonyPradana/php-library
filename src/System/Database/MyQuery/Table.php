<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\Interfaces\EscapeQuery;
use System\Database\MyPDO;

class Table
{
    protected ?EscapeQuery $escape_query = null;

    public function __construct(
        protected string|InnerQuery $table_name,
        protected MyPDO $PDO,
    ) {
    }

    public function setEscape(?EscapeQuery $escape_query): self
    {
        $this->escape_query = $escape_query;

        return $this;
    }

    /**
     * Perform insert query.
     *
     * @return Insert
     */
    public function insert()
    {
        $insert = new Insert($this->table_name, $this->PDO);
        $insert->setEscape($this->escape_query);

        return $insert;
    }

    /**
     * Perform replace query.
     *
     * @return Replace
     */
    public function replace()
    {
        $replace = new Replace($this->table_name, $this->PDO);
        $replace->setEscape($this->escape_query);

        return $replace;
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
        if ($this->table_name instanceof InnerQuery) {
            $this->table_name->setEscape($this->escape_query);
        }

        $select = new Select($this->table_name, $select_columns, $this->PDO);
        $select->setEscape($this->escape_query);

        return $select;
    }

    /**
     * Perform update query.
     *
     * @return Update
     */
    public function update()
    {
        $update = new Update($this->table_name, $this->PDO);
        $update->setEscape($this->escape_query);

        return $update;
    }

    /**
     * Perform delete query.
     *
     * @return Delete
     */
    public function delete()
    {
        $delete = new Delete($this->table_name, $this->PDO);
        $delete->setEscape($this->escape_query);

        return $delete;
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
