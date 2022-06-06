<?php

namespace System\Database\MyQuery\Join;

abstract class AbstractJoin
{
    protected $_mainTable     = '';
    protected $_tableName     = '';
    protected $_colomnName    = '';
    protected $_compereColumn = [];

    public function __invoke(string $main_table)
    {
        $this->_mainTable = $main_table;

        return $this;
    }

    public function __toString()
    {
        return $this->stringJoin();
    }

    /**
     * Instance of class.
     *
     * @param string      $ref_table
     *                               Name of the table want to join
     * @param string      $id
     *                               main id of the table
     * @param string|null $ref_id
     *                               id of the table want to join, null mean same with main id
     */
    public static function ref(string $ref_table, string $id, ?string $ref_id = null)
    {
        return (new static())
      ->tableRef($ref_table)
      ->compare($id, $ref_id);
    }

    // setter

    /**
     * set main table / master table.
     *
     * @param string $main_table Name of the master table
     */
    public function table(string $main_table)
    {
        $this->_mainTable = $main_table;

        return $this;
    }

    /**
     * Set table reference.
     *
     * @param string $ref_table Name of the ref table
     */
    public function tableRef(string $ref_table)
    {
        $this->_tableName = $ref_table;

        return $this;
    }

    /**
     * set main table and ref table.
     *
     * @param string $main_table Name of the master table
     * @param string $ref_table  Name of the ref table
     */
    public function tableRelation(string $main_table, string $ref_table)
    {
        $this->_mainTable = $main_table;
        $this->_tableName = $ref_table;

        return $this;
    }

    /**
     * Compare identical two table.
     *
     * @param string $main_coumn identical of the main table column
     * @param string $main_coumn identical of the ref table column
     */
    public function compare(string $main_column, string $compire_column = null)
    {
        $compire_column = $compire_column ?? $main_column;

        $this->_compereColumn[] = [
      $main_column, $compire_column,
    ];

        return $this;
    }

    // getter
    /**
     * Get string of raw join builder.
     *
     * @return string String of raw join builder
     */
    public function stringJoin(): string
    {
        return $this->joinBuilder();
    }

    // main

    /**
     * Setup bulider.
     *
     * @return string Raw of builder join
     */
    protected function joinBuilder(): string
    {
        return $this->_stringJoin;
    }

    /**
     * Get string of compare join
     * (ex: a.b = c.d).
     */
    protected function splitJoin(): string
    {
        $on = [];
        foreach ($this->_compereColumn as $column) {
            $masterColumn  = $column[0];
            $compireColumn = $column[1];

            $on[] = "$this->_mainTable.$masterColumn = $this->_tableName.$compireColumn";
        }

        return implode(' AND ', $on);
    }
}
