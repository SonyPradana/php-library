<?php

namespace System\Database\MyQuery\Traits;

use System\Database\MyQuery\Select;

/**
 * Trait to provide conditon under class extend with Query::class.
 */
trait ConditionTrait
{
    public function equal(string $bind, string $value)
    {
        $this->compare($bind, '=', $value, false);

        return $this;
    }

    public function like(string $bind, string $value)
    {
        $this->compare($bind, 'LIKE', $value, false);

        return $this;
    }

    public function where(string $where_condition, ?array $binder = null)
    {
        $this->_where[] = $where_condition;

        if ($binder != null) {
            $this->_binder = array_merge($this->_binder, $binder);
        }

        return $this;
    }

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

    public function whereExist(Select $select)
    {
        $this->_where[] = 'EXISTS (' . $select->__toString() . ')';
        $this->_binder  = array_merge($this->_binder, $select->_binder);

        return $this;
    }

    public function whereNotExist(Select $select)
    {
        $this->_where[] = 'NOT EXISTS (' . $select->__toString() . ')';
        $this->_binder  = array_merge($this->_binder, $select->_binder);

        return $this;
    }
}
