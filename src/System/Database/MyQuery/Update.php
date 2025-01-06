<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;
use System\Database\MyQuery\Join\AbstractJoin;
use System\Database\MyQuery\Traits\ConditionTrait;
use System\Database\MyQuery\Traits\SubQueryTrait;

class Update extends Execute
{
    use ConditionTrait;
    use SubQueryTrait;

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
     * Insert set value (single).
     *
     * @param array<string, string|int|bool|null> $values Array of bing and value
     *
     * @return self
     */
    public function values($values)
    {
        foreach ($values as $key => $value) {
            $this->value($key, $value);
        }

        return $this;
    }

    /**
     * Insert set value (single).
     *
     * @param string               $bind  Pdo bind
     * @param string|int|bool|null $value Value of the bind
     *
     * @return self
     */
    public function value(string $bind, $value)
    {
        $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

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
        // overide master table
        $ref_table->table($this->_table);

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
        $setter = [];
        foreach ($this->_binds as $bind) {
            if ($bind->hasColumName()) {
                $setter[] = $bind->getColumnName() . ' = ' . $bind->getBind();
            }
        }

        $build          = [];
        $build['join']  = $this->getJoin();
        $build[]        = 'SET ' . implode(', ', $setter);
        $build['where'] = $this->getWhere();

        $query_parts = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->_query = "UPDATE {$this->_table} {$query_parts}";
    }
}
