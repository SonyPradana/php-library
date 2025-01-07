<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyPDO;

class Insert extends Execute
{
    /**
     * @var array<string, string>
     */
    private ?array $duplicate_key = null;

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
     *  Value query builder (key => value).
     *
     * @param array<string, string|int|bool|null> $values Insert values
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
     * @param string|int|bool|null $value
     *
     * @return self
     */
    public function value(string $bind, $value)
    {
        $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    /**
     * Added multy rows (values).
     *
     * @param array<int, array<string, string|int|bool|null>> $rows
     */
    public function rows(array $rows): self
    {
        foreach ($rows as $index => $values) {
            foreach ($values as $bind => $value) {
                $this->_binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_' . $index . '_');
            }
        }

        return $this;
    }

    /**
     * On duplicate key update.
     */
    public function on(string $column, ?string $value = null): self
    {
        $this->duplicate_key[$column] = $value ?? "VALUES({$column})";

        return $this;
    }

    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructur();

        $strings_binds = [];
        /** @var array<int, array<int, string>> */
        $chunk         = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $strings_binds[] = '(' . implode(', ', $group) . ')';
        }

        $builds              = [];
        $builds['column']    = '(' . implode(', ', $columns) . ')';
        $builds['values']    = 'VALUES';
        $builds['binds']     = implode(', ', $strings_binds);
        $builds['keyUpdate'] = $this->getDuplicateKeyUpdate();
        $string_build        = implode(' ', array_filter($builds, fn ($item) => $item !== ''));

        $this->_query = "INSERT INTO {$this->_table} {$string_build}";

        return $this->_query;
    }

    private function getDuplicateKeyUpdate(): string
    {
        if (null === $this->duplicate_key) {
            return '';
        }

        $keys = [];
        foreach ($this->duplicate_key as $key => $value) {
            $keys[] = "{$key} = {$value}";
        }

        return 'ON DUPLICATE KEY UPDATE ' . implode(', ', $keys);
    }
}
