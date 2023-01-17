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

    public function __construct(string $data_type)
    {
        $this->data_type = $data_type;
        $this->null_able = '';
        $this->default = '';
        $this->auto_increment = '';
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
        ];

        return implode(' ', array_filter($collumn, fn($item) => $item !== ''));
    }

    public function notNull(bool $null = true): self
    {
        $this->null_able = $null ? 'NOT NULL' : '';

        return $this;
    }

    public function null(bool $null = true): self
    {
        return $this->notNull(!null);
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

}
