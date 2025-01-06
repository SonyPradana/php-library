<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Traits;

use System\Database\MyQuery\Bind;

/**
 * Trait to provide conditon under class extend with Query::class.
 */
trait ConditionTrait
{
    /**
     * Insert 'equal' condition in (query bulider).
     *
     * @param string               $bind  Bind
     * @param string|int|bool|null $value Value of bind
     *
     * @return self
     */
    public function equal(string $bind, $value)
    {
        $this->compare($bind, '=', $value, false);

        return $this;
    }

    /**
     * Insert 'like' condition in (query bulider).
     *
     * @param string               $bind  Bind
     * @param string|int|bool|null $value Value of bind
     *
     * @return self
     */
    public function like(string $bind, $value)
    {
        $this->compare($bind, 'LIKE', $value, false);

        return $this;
    }

    /**
     * Insert 'where' condition in (query bulider).
     *
     * @param string                   $where_condition Spesific column name
     * @param array<int, mixed[]|Bind> $binder          Bind and value (use for 'in')
     *
     * @return self
     */
    public function where(string $where_condition, array $binder = [])
    {
        $this->_where[] = $where_condition;

        foreach ($binder as $bind) {
            if ($bind instanceof Bind) {
                $this->_binds[] = $bind;
                continue;
            }
            $this->_binds[] = Bind::set($bind[0], $bind[1])->prefixBind('');
        }

        return $this;
    }

    /**
     * Insert 'between' condition in (query bulider).
     *
     * @param string $column_name Spesific column name
     * @param int    $value_1     Between start
     * @param int    $value_2     Between end
     *
     * @return self
     */
    public function between(string $column_name, $value_1, $value_2)
    {
        $table_name = null === $this->_sub_query ? $this->_table : $this->_sub_query->getAlias();

        $this->where(
            "({$table_name}.{$column_name} BETWEEN :b_start AND :b_end)",
            []
        );

        $this->_binds[] = Bind::set('b_start', $value_1);
        $this->_binds[] = Bind::set('b_end', $value_2);

        return $this;
    }

    /**
     * Insert 'in' condition (query bulider).
     *
     * @param string                                  $column_name Spesific column name
     * @param array<int|string, string|int|bool|null> $value       Bind and value (use for 'in')
     *
     * @return self
     */
    public function in(string $column_name, $value)
    {
        $binds  = [];
        $binder = [];
        foreach ($value as $key => $bind) {
            $binds[]  = ":in_$key";
            $binder[] = [":in_$key", $bind];
        }
        $bindString = implode(', ', $binds);
        $table_name = null === $this->_sub_query ? "{$this->_table}" : $this->_sub_query->getAlias();

        $this->where(
            "({$table_name}.{$column_name} IN ({$bindString}))",
            $binder
        );

        return $this;
    }

    /**
     * Where statment setter,
     * menambahakan syarat pada query builder.
     *
     * @param string               $bind        Key atau nama column
     * @param string               $comparation tanda hubung yang akan digunakan (AND|OR|>|<|=|LIKE)
     * @param string|int|bool|null $value       Value atau nilai dari key atau nama column
     *
     * @return self
     */
    public function compare(string $bind, string $comparation, $value, bool $bindValue = false)
    {
        $escape_bind           = str_replace('.', '__', $bind);
        $this->_binds[]        = Bind::set($escape_bind, $value);
        $this->_filters[$bind] = [
            'value'       => $value,
            'comparation' => $comparation,
            'bind'        => $escape_bind,
            $bindValue,
        ];

        return $this;
    }

    /**
     * Setter strict mode.
     *
     * True = operator using AND,
     * False = operator using OR
     */
    public function strictMode(bool $strict): self
    {
        $this->_strict_mode = $strict;

        return $this;
    }
}
