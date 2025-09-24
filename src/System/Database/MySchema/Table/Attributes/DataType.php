<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table\Attributes;

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
        if (0 === $lenght) {
            return $this->datatype = new Constraint('int');
        }

        return $this->datatype = new Constraint("int({$lenght})");
    }

    public function tinyint(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('tinyint');
        }

        return $this->datatype = new Constraint("tinyint({$lenght})");
    }

    public function smallint(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('smallint');
        }

        return $this->datatype = new Constraint("smallint({$lenght})");
    }

    public function bigint(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('bigint');
        }

        return $this->datatype = new Constraint("bigint({$lenght})");
    }

    public function float(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('float');
        }

        return $this->datatype = new Constraint("float({$lenght})");
    }

    public function decimal(int $precision = 10, int $scale = 2): Constraint
    {
        return $this->datatype = new Constraint("decimal($precision, $scale)");
    }

    public function double(int $precision = 10, int $scale = 2): Constraint
    {
        return $this->datatype = new Constraint("double($precision, $scale)");
    }

    public function boolean(): Constraint
    {
        return $this->datatype = new Constraint('boolean');
    }

    // date

    public function time(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('time');
        }

        return $this->datatype = new Constraint("time({$lenght})");
    }

    public function timestamp(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('timestamp');
        }

        return $this->datatype = new Constraint("timestamp({$lenght})");
    }

    public function date(): Constraint
    {
        return $this->datatype = new Constraint('date');
    }

    public function datetime(): Constraint
    {
        return $this->datatype = new Constraint('datetime');
    }

    public function year(): Constraint
    {
        return $this->datatype = new Constraint('year');
    }

    // text

    public function char(int $lenght = 255): Constraint
    {
        return $this->datatype = new Constraint("char({$lenght})");
    }

    public function varchar(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('varchar');
        }

        return $this->datatype = new Constraint("varchar({$lenght})");
    }

    public function text(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('text');
        }

        return $this->datatype = new Constraint("text({$lenght})");
    }

    public function blob(int $lenght = 0): Constraint
    {
        if (0 === $lenght) {
            return $this->datatype = new Constraint('blob');
        }

        return $this->datatype = new Constraint("blob({$lenght})");
    }

    public function json(): Constraint
    {
        return $this->datatype = new Constraint('json');
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
}
