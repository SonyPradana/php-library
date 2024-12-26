<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Traits;

use System\Database\MyQuery\Select;

/**
 * Sub where query trait.
 */
trait SubQueryTrait
{
    /**
     * Add sub query to where statement.
     */
    public function whereClause(string $clause, Select $select): self
    {
        $binds          = (fn () => $this->{'_binds'})->call($select);
        $this->_where[] = implode(' ', [$clause, '(', (string) $select, ')']);
        foreach ($binds as $bind) {
            $this->_binds[] = $bind;
        }

        return $this;
    }

    public function whereCompare(string $column_name, string $operator, Select $select): self
    {
        return $this->whereClause($column_name . ' ' . $operator, $select);
    }

    /**
     * Added 'where exists' condition (query bulider).
     *
     * @param Select $select Select class
     *
     * @return self
     */
    public function whereExist(Select $select)
    {
        return $this->whereClause('EXISTS', $select);
    }

    /**
     * Added 'where not exists' condition (query bulider).
     *
     * @param Select $select Select class
     *
     * @return self
     */
    public function whereNotExist(Select $select)
    {
        return $this->whereClause('NOT EXISTS', $select);
    }

    /**
     * Added 'where equal' condition (query bulider).
     */
    public function whereEqual(string $column_name, Select $select): self
    {
        return $this->whereClause($column_name . ' =', $select);
    }

    /**
     * Added 'where like' condition (query bulider).
     */
    public function whereLike(string $column_name, Select $select): self
    {
        return $this->whereClause($column_name . ' LIKE', $select);
    }

    /**
     * Added 'where in' condition (query bulider).
     */
    public function whereIn(string $column_name, Select $select): self
    {
        return $this->whereClause($column_name . ' IN', $select);
    }
}
