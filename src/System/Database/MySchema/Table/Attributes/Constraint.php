<?php

declare(strict_types=1);

namespace System\Database\MySchema\Table\Attributes;

class Constraint
{
    /** @var string */
    protected $data_type;
    /** @var string */
    protected $null_able;
    /** @var string */
    protected $default;
    /** @var string */
    protected $auto_increment;
    /** @var string */
    protected $order;
    /** @var string */
    protected $raw;

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

    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
