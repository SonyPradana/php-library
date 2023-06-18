<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table\Attributes;

class AlterDataType extends DataType
{
    public function after(string $column): void
    {
        $this->datatype = "AFTER `{$column}`";
    }

    public function first(): void
    {
        $this->datatype = 'FIRST';
    }
}
