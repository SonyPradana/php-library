<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\AbstractJoin;
use System\Database\MyQuery\Traits\ConditionTrait;

final class Select extends Fetch
{
    use ConditionTrait;

    /**
     * @param string   $table_name   Table name
     * @param string[] $columns_name Selected cloumn
     * @param MyPDO    $PDO          MyPDO class
     * @param string[] $options      Add costume option (eg: query)
     *
     * @return void
     */
    public function __construct(string $table_name, array $columns_name, MyPDO $PDO, array $options = null)
    {
        $this->_table  = $table_name;
        $this->_column = $columns_name;
        $this->PDO     = $PDO;

        // defaul query
        if (count($this->_column) > 1) {
            $this->_column = array_map(fn ($e) => "`$e`", $this->_column);
        }

        $column       = implode(', ', $columns_name);
        $this->_query = $options['query'] ?? "SELECT $column FROM `$this->_table`";
    }

    public function __toString()
    {
        return $this->builder();
    }

    /**
     * Instance of `Select::class`.
     *
     * @param string   $table_name  Table name
     * @param string[] $column_name Selected column
     * @param MyPDO    $PDO         MyPdo
     *
     * @return Select
     */
    public static function from(string $table_name, array $column_name, MyPDO $PDO)
    {
        return new static($table_name, $column_name, $PDO);
    }

    /**
     * Membuat join table
     *  - inner join
     *  - left join
     *  - right join
     *  - full join.
     *
     * @param AbstractJoin $ref_table Configure type of join
     *
     * @return self
     */
    public function join(AbstractJoin $ref_table)
    {
        // overide master table
        $ref_table->table($this->_table);

        $this->_join[] = $ref_table->stringJoin();

        return $this;
    }

    // sort, order, grouping

    /**
     * Set data start for feact all data.
     *
     * @param int $limit_start limit start
     * @param int $limit_end   limit end
     *
     * @return self
     */
    public function limit(int $limit_start, int $limit_end)
    {
        $this->_limit_start = $limit_start;
        $this->_limit_end   = $limit_end;

        return $this;
    }

    /**
     * Set data start for feact all data.
     *
     * @param int $value limit start default is 0
     *
     * @return self
     */
    public function limitStart(int $value)
    {
        $this->_limit_start = $value;

        return $this;
    }

    /**
     * Set data end for feact all data
     * zero value meaning no data show.
     *
     * @param int $value limit start default
     *
     * @return self
     */
    public function limitEnd(int $value)
    {
        $this->_limit_end = $value;

        return $this;
    }

    /**
     * Set sort column and order
     * column name must register.
     *
     * @return self
     */
    public function order(string $column_name, int $order_using = MyQuery::ORDER_ASC, string $belong_to = null)
    {
        $order             = $order_using == 0 ? 'ASC' : 'DESC';
        $belong_to         = $belong_to ?? $this->_table;
        $this->_sort_order = "ORDER BY `$belong_to`.`$column_name` $order";

        return $this;
    }

    /**
     * Setter strict mode.
     *
     * True = operator using AND,
     * False = operator using OR
     *
     * @param bool $value True where statment operation using AND
     *
     * @return self
     */
    public function strictMode(bool $value)
    {
        $this->_strict_mode = $value;

        return $this;
    }

    /**
     * Build SQL query syntac for bind in next step.
     */
    protected function builder(): string
    {
        $column = implode(', ', $this->_column);

        // join
        $join = count($this->_join) == 0
            ? ''
            : implode(' ', $this->_join);

        // where
        $where  = $this->getWhere();
        $where  = $where == '' && count($this->_join) > 0
            ? ''
            : $where;

        // sort order
        $sort_order = $this->_sort_order == ''
            ? ''
            : " $this->_sort_order";

        // limit
        $limit = $this->_limit_start < 0
            ? " LIMIT $this->_limit_end"
            : " LIMIT $this->_limit_start, $this->_limit_end";

        $limit = $this->_limit_end == 0
            ? ''
            : $limit;

        $condition = $join . $where . $sort_order . $limit;

        return $this->_query = "SELECT $column FROM `$this->_table` $condition";
    }
}
