<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table\Attributes\Alter;

class DataType
{
    /** @var string */
    private $name;
    /** @var string|Constraint */
    private $datatype;

    public function __construct(string $column_name)
    {
        $this->name     = $column_name;
        $this->datatype = '';
    }

    public function __toString()
    {
        return $this->query();
    }

    private function query(): string
    {
        return $this->name . ' ' . $this->datatype;
    }

    // number

    public function int(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('int');
        }

        return $this->datatype = new Constraint("int($lenght)");
    }

    public function tinyint(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('tinyint');
        }

        return $this->datatype = new Constraint("tinyint($lenght)");
    }

    public function smallint(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('smallint');
        }

        return $this->datatype = new Constraint("smallint($lenght)");
    }

    public function bigint(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('bigint');
        }

        return $this->datatype = new Constraint("bigint($lenght)");
    }

    public function float(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('float');
        }

        return $this->datatype = new Constraint("float($lenght)");
    }

    // date

    public function time(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('time');
        }

        return $this->datatype = new Constraint("time($lenght)");
    }

    public function timestamp(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('timestamp');
        }

        return $this->datatype = new Constraint("timestamp($lenght)");
    }

    public function date(): Constraint
    {
        return $this->datatype = new Constraint('date');
    }

    // text

    public function varchar(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('varchar');
        }

        return $this->datatype = new Constraint("varchar($lenght)");
    }

    public function text(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('text');
        }

        return $this->datatype = new Constraint("text($lenght)");
    }

    public function blob(int $lenght = 0): Constraint
    {
        if ($lenght === 0) {
            return $this->datatype = new Constraint('blob');
        }

        return $this->datatype = new Constraint("blob($lenght)");
    }

    /**
     * @param string[] $enums
     */
    public function enum(array $enums): Constraint
    {
        $enums = array_map(fn ($item) => "'{$item}'", $enums);
        $enum  = implode(', ', $enums);

        return $this->datatype = new Constraint("ENUM ({$enum})");
    }

    public function after(string $column): void
    {
        $this->datatype = "AFTER {$column}";
    }

    public function first(): void
    {
        $this->datatype = 'FIRST';
    }
}
