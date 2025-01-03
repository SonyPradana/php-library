<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\AbstractJoin;
use System\Database\MyQuery\Traits\ConditionTrait;
use System\Database\MyQuery\Traits\SubQueryTrait;

final class Select extends Fetch
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * @param string|InnerQuery $table_name   Table name
     * @param string[]          $columns_name Selected cloumn
     * @param MyPDO             $PDO          MyPDO class
     * @param string[]          $options      Add costume option (eg: query)
     *
     * @return void
     */
    public function __construct($table_name, array $columns_name, MyPDO $PDO, ?array $options = null)
    {
        $this->_sub_query = $table_name instanceof InnerQuery ? $table_name : new InnerQuery(table: $table_name);
        $this->_column    = $columns_name;
        $this->PDO        = $PDO;

        // inherit bind from sub query
        if ($table_name instanceof InnerQuery) {
            $this->_binds = $table_name->getBind();
        }

        $column       = implode(', ', $columns_name);
        $this->_query = $options['query'] ?? "SELECT {$column} FROM { $this->_sub_query}";
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
     * Join statment:
     *  - inner join
     *  - left join
     *  - right join
     *  - full join.
     */
    public function join(AbstractJoin $ref_table): self
    {
        // overide master table
        $ref_table->table($this->_sub_query->getAlias());

        $this->_join[] = $ref_table->stringJoin();
        $binds         = (fn () => $this->{'sub_query'})->call($ref_table);

        if (null !== $binds) {
            $this->_binds = array_merge($this->_binds, $binds->getBind());
        }

        return $this;
    }

    private function joinBuilder(): string
    {
        return 0 === count($this->_join)
            ? ''
            : implode(' ', $this->_join)
        ;
    }

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
        $this->limitStart($limit_start);
        $this->limitEnd($limit_end);

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
        $this->_limit_start = $value < 0 ? 0 : $value;

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
        $this->_limit_end = $value < 0 ? 0 : $value;

        return $this;
    }

    /**
     * Set offest.
     *
     * @param int $value offet
     *
     * @return self
     */
    public function offset(int $value)
    {
        $this->_offset = $value < 0 ? 0 : $value;

        return $this;
    }

    /**
     * Set limit using limit and offset.
     */
    public function limitOffset(int $limit, int $offset): self
    {
        return $this
            ->limitStart($limit)
            ->limitEnd(0)
            ->offset($offset);
    }

    /**
     * Set sort column and order
     * column name must register.
     *
     * @return self
     */
    public function order(string $column_name, int $order_using = MyQuery::ORDER_ASC, ?string $belong_to = null)
    {
        $order = 0 === $order_using ? 'ASC' : 'DESC';
        $belong_to ??= null === $this->_sub_query ? $this->_table : $this->_sub_query->getAlias();
        $res = "{$belong_to}.{$column_name}";

        $this->_sort_order[$res] = $order;

        return $this;
    }

    /**
     * Set sort column and order
     * with Column if not null.
     */
    public function orderIfNotNull(string $column_name, int $order_using = MyQuery::ORDER_ASC, ?string $belong_to = null): self
    {
        return $this->order("{$column_name} IS NOT NULL", $order_using, $belong_to);
    }

    /**
     * Set sort column and order
     * with Column if null.
     */
    public function orderIfNull(string $column_name, int $order_using = MyQuery::ORDER_ASC, ?string $belong_to = null): self
    {
        return $this->order("{$column_name} IS NULL", $order_using, $belong_to);
    }

    /**
     * Adds one or more columns to the
     * GROUP BY clause of the SQL query.
     */
    public function groupBy(string ...$groups): self
    {
        $this->_group_by = $groups;

        return $this;
    }

    /**
     * Build SQL query syntac for bind in next step.
     */
    protected function builder(): string
    {
        $column = implode(', ', $this->_column);

        $build = [];

        $build['join']       = $this->joinBuilder();
        $build['where']      = $this->getWhere();
        $build['group_by']   = $this->getGroupBy();
        $build['sort_order'] = $this->getOrderBy();
        $build['limit']      = $this->getLimit();

        $condition = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->_query = "SELECT {$column} FROM {$this->_sub_query} {$condition}";
    }

    /**
     * Get formated combine limit and offset.
     */
    private function getLimit(): string
    {
        $limit = $this->_limit_end > 0 ? "LIMIT $this->_limit_end" : '';

        if ($this->_limit_start === 0) {
            return $limit;
        }

        if ($this->_limit_end === 0 && $this->_offset > 0) {
            return "LIMIT $this->_limit_start OFFSET $this->_offset";
        }

        return "LIMIT $this->_limit_start, $this->_limit_end";
    }

    private function getGroupBy(): string
    {
        if ([] === $this->_group_by) {
            return '';
        }

        $group_by = implode(', ', $this->_group_by);

        return "GROUP BY {$group_by}";
    }

    private function getOrderBy(): string
    {
        if ([] === $this->_sort_order) {
            return '';
        }

        $orders = [];
        foreach ($this->_sort_order as $column => $order) {
            $orders[] = "{$column} {$order}";
        }

        $orders = implode(', ', $orders);

        return "ORDER BY {$orders}";
    }

    /**
     * @param array<string, string> $sort_ordder
     */
    public function sortOrderRef(int $limit_start, int $limit_end, int $offset, $sort_ordder): void
    {
        $this->_limit_start = $limit_start;
        $this->_limit_end   = $limit_end;
        $this->_offset      = $offset;
        $this->_sort_order  = $sort_ordder;
    }
}
