<?php

namespace System\Database\MySchema\Table\Attributes;

class DataType
{
    private $name;
    private $datatype;

    public function __construct(string $column_name)
    {
        $this->name     = $column_name;
        $this->datatype = '';
    }

    public function __toString()
    {
        return '`' . $this->name . '` ' . $this->datatype;
    }

    // number

    public function int(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'int';
        }

        return $this->datatype = "int($lenght)";
    }

    public function tinyint(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'tinyint';
        }

        return $this->datatype = "tinyint($lenght)";
    }

    public function smallint(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'smallint';
        }

        return $this->datatype = "smallint($lenght)";
    }

    public function bigint(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'bigint';
        }

        return $this->datatype = "bigint($lenght)";
    }

    public function float(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'float';
        }

        return $this->datatype = "float($lenght)";
    }

    // date

    public function time(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'time';
        }

        return $this->datatype = "time($lenght)";
    }

    public function timestamp(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'timestamp';
        }

        return $this->datatype = "timestamp($lenght)";
    }

    public function date(): string
    {
        return $this->datatype = 'date';
    }

    // text

    public function varchar(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'varchar';
        }

        return $this->datatype = "varchar($lenght)";
    }

    public function text(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'text';
        }

        return $this->datatype = "text($lenght)";
    }

    public function blob(int $lenght = 0): string
    {
        if ($lenght === 0) {
            return $this->datatype = 'blob';
        }

        return $this->datatype = "blob($lenght)";
    }
}
