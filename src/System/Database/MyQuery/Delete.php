<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery\Join\AbstractJoin;
use System\Database\MyQuery\Traits\ConditionTrait;
use System\Database\MyQuery\Traits\SubQueryTrait;

class Delete extends Execute
{
    use ConditionTrait;
    use SubQueryTrait;

    protected ?string $alias = null;

    public function __construct(string $table_name, MyPDO $PDO)
    {
        $this->_table = $table_name;
        $this->PDO    = $PDO;
    }

    public function __toString()
    {
        return $this->builder();
    }

    /**
     * Set alias for the table.
     * If using an alias, conditions with binding values will be ignored,
     * except when using subqueries, clause in join also will be generate as alias.
     */
    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
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
        $table = $this->alias ?? $this->_table;
        $ref_table->table($table);

        $this->_join[] = $ref_table->stringJoin();
        $binds         = (fn () => $this->{'sub_query'})->call($ref_table);

        if (null !== $binds) {
            $this->_binds = array_merge($this->_binds, $binds->getBind());
        }

        return $this;
    }

    private function getJoin(): string
    {
        return 0 === count($this->_join)
            ? ''
            : implode(' ', $this->_join)
        ;
    }

    protected function builder(): string
    {
        $build = [];

        $build['join']  = $this->getJoin();
        $build['where'] = $this->getWhere();

        $query_parts = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->_query =  null === $this->alias
            ? "DELETE FROM {$this->_table} {$query_parts}"
            : "DELETE {$this->alias} FROM {$this->_table} AS {$this->alias} {$query_parts}";
    }
}
