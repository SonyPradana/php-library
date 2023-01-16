<?php

namespace System\Database\MySchema\Table\Attributes;

class DataType
{
    private $name;
    private $datatype;

    public function __construct(string $column_name)
    {
        $this->name = $column_name;
    }

    public function __toString()
    {
        return $this->name . ' ' . $this->datatype;
    }

    public function int(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'int';
        }

        return $this->datatype = "int($lenght)";
    }

    public function varchar(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'varchar';
        }

        return $this->datatype = "varchar($lenght)";
    }
}
