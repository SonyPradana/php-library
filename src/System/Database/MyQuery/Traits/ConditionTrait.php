<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Traits;

use System\Database\MyQuery\Select;

/**
 * Trait to provide conditon under class extend with Query::class.
 */
trait ConditionTrait
{
    /**
     * Insert 'equal' condition in (query bulider).
     *
     * @param string $bind  Bind
     * @param string $value Value of bind
     *
     * @return self
     */
    public function equal(string $bind, string $value)
    {
        $this->compare($bind, '=', $value, false);

        return $this;
    }

    /**
     * Insert 'like' condition in (query bulider).
     *
     * @param string $bind  Bind
     * @param string $value Value of bind
     *
     * @return self
     */
    public function like(string $bind, string $value)
    {
        $this->compare($bind, 'LIKE', $value, false);

        return $this;
    }

    /**
     * Insert 'where' condition in (query bulider).
     *
     * @param string                         $where_condition Spesific column name
     * @param array<int, array<int, string>> $binder          Bind and value (use for 'in')
     *
     * @return self
     */
    public function where(string $where_condition, ?array $binder = null)
    {
        $this->_where[] = $where_condition;

        if ($binder !== null) {
            $this->_binder = array_merge($this->_binder, $binder);
        }

        return $this;
    }

    /**
     * Insert 'between' condition in (query bulider).
     *
     * @param string $column_name Spesific column name
     * @param string $val_1       Between
     * @param string $val_2       Between
     *
     * @return self
     */
    public function between(string $column_name, string $val_1, string $val_2)
    {
        $this->where(
            "(`$this->_table`.`$column_name` BETWEEN :b_start AND :b_end)",
            [
                [':b_start', $val_1],
                [':b_end', $val_2],
            ]
        );

        return $this;
    }

    /**
     * Insert 'in' condition (query bulider).
     *
     * @param string   $column_name Spesific column name
     * @param string[] $val         Bind and value (use for 'in')
     *
     * @return self
     */
    public function in(string $column_name, array $val)
    {
        $binds  = [];
        $binder = [];
        foreach ($val as $key => $bind) {
            $binds[]  = ":in_$key";
            $binder[] = [":in_$key", $bind];
        }
        $bindString = implode(', ', $binds);

        $this->where(
            "(`$this->_table`.`$column_name` IN ($bindString))",
            $binder
        );

        return $this;
    }

    /**
     * Insert 'where exists' condition (query bulider).
     *
     * @param Select $select Select class
     *
     * @return self
     */
    public function whereExist(Select $select)
    {
        $this->_where[] = 'EXISTS (' . $select->__toString() . ')';
        $this->_binder  = array_merge($this->_binder, $select->_binder);

        return $this;
    }

    /**
     * Insert 'where not exists' condition (query bulider).
     *
     * @param Select $select Select class
     *
     * @return self
     */
    public function whereNotExist(Select $select)
    {
        $this->_where[] = 'NOT EXISTS (' . $select->__toString() . ')';
        $this->_binder  = array_merge($this->_binder, $select->_binder);

        return $this;
    }
}
