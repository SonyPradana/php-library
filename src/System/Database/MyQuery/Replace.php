<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

class Replace extends Insert
{
    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructur();

        $strings_binds = [];
        /** @var array<int, array<int, string>> */
        $chunk         = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $strings_binds[] = '(' . implode(', ', $group) . ')';
        }

        $stringBinds  = implode(', ', $strings_binds);
        $stringColumn = implode(', ', $columns);

        return $this->_query = "REPLACE INTO {$this->_table} ({$stringColumn}) VALUES {$stringBinds}";
    }
}
