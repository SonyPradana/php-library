<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Join;

use System\Database\MyQuery\InnerQuery;

abstract class AbstractJoin
{
    /**
     * @var string
     */
    protected $_mainTable     = '';

    /**
     * @var string
     */
    protected $_tableName     = '';

    /**
     * @var string
     */
    protected $_colomnName    = '';

    /**
     * @var string[]
     */
    protected $_compereColumn = [];

    /**
     * @var string
     */
    protected $_stringJoin    = '';

    protected ?InnerQuery $sub_query = null;

    final public function __construct()
    {
    }

    /**
     * @return self
     */
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
     * @param string|InnerQuery $ref_table Name of the table want to join or sub query
     * @param string            $id        Main id of the table
     * @param string|null       $ref_id    Id of the table want to join, null means same as main id
     */
    public static function ref($ref_table, string $id, ?string $ref_id = null): AbstractJoin
    {
        $instance = new static();

        if ($ref_table instanceof InnerQuery) {
            return $instance
                ->clausa($ref_table)
                ->compare($id, $ref_id);
        }

        return $instance
            ->tableRef($ref_table)
            ->compare($id, $ref_id);
    }

    // setter

    /**
     * set main table / master table.
     *
     * @param string $main_table Name of the master table
     *
     * @return self
     */
    public function table(string $main_table)
    {
        $this->_mainTable = $main_table;

        return $this;
    }

    public function clausa(InnerQuery $select): self
    {
        $this->sub_query  = $select;
        $this->_tableName = $select->getAlias();

        return $this;
    }

    /**
     * Set table reference.
     *
     * @param string $ref_table Name of the ref table
     *
     * @return self
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
     *
     * @return self
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
     * @param string $main_column    Identical of the main table column
     * @param string $compire_column Identical of the ref table column
     *
     * @return self
     */
    public function compare(string $main_column, ?string $compire_column = null)
    {
        $compire_column ??= $main_column;

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

    protected function getAlias(): string
    {
        return null === $this->sub_query ? $this->_tableName : (string) $this->sub_query;
    }
}
