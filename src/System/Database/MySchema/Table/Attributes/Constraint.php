<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table\Attributes;

class Constraint
{
    /** @var string */
    private $data_type;
    /** @var string */
    private $null_able;
    /** @var string */
    private $default;
    /** @var string */
    private $auto_increment;
    /** @var string */
    private $order;
    /** @var string */
    private $raw;

    public function __construct(string $data_type)
    {
        $this->data_type      = $data_type;
        $this->null_able      = '';
        $this->default        = '';
        $this->auto_increment = '';
        $this->raw            = '';
        $this->order          = '';
    }

    public function __toString()
    {
        return $this->query();
    }

    private function query(): string
    {
        $collumn = [
            $this->data_type,
            $this->null_able,
            $this->default,
            $this->auto_increment,
            $this->raw,
            $this->order,
        ];

        return implode(' ', array_filter($collumn, fn ($item) => $item !== ''));
    }

    public function notNull(bool $null = true): self
    {
        $this->null_able = $null ? 'NOT NULL' : '';

        return $this;
    }

    public function null(bool $null = true): self
    {
        return $this->notNull(!$null);
    }

    public function default(string $default): self
    {
        $this->default = "'$default'";

        return $this;
    }

    public function autoIncrement(bool $incremnet): self
    {
        $this->auto_increment = $incremnet ? 'AUTO_INCREMENT' : '';

        return $this;
    }

    public function increment(bool $incremnet): self
    {
        return $this->autoIncrement($incremnet);
    }

    public function after(string $column): self
    {
        $this->order = "AFTER `{$column}`";

        return $this;
    }

    /**
     * Only use on Alter Column.
     */
    public function first(): self
    {
        $this->order = 'FIRST';

        return $this;
    }

    /**
     * Only use on Alter Column.
     */
    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
