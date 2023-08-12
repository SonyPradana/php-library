<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

/**
 * @internal
 */
final class Bind
{
    /**
     * bind name (required).
     *
     * @var string
     */
    private $bind;

    /**
     * Bind value (required).
     *
     * @var mixed
     */
    private $bind_value;

    /**
     * represented column name (optional).
     *
     * @var string
     */
    private $column_name;

    /**
     * set prefix bind (bind name not same with column name).
     *
     * @var string
     */
    private $prefix_bind;

    /**
     * @param mixed $value
     */
    public function __construct(string $bind, $value, string $column_name = '')
    {
        $this->bind        = $bind;
        $this->bind_value  = $value;
        $this->column_name = $column_name;
        $this->prefix_bind = ':';
    }

    /**
     * @param mixed $value
     */
    public static function set(string $bind, $value, string $column_name = ''): self
    {
        return new static($bind, $value, $column_name);
    }

    public function prefixBind(string $prefix): self
    {
        $this->prefix_bind = $prefix;

        return $this;
    }

    public function setBind(string $bind): self
    {
        $this->bind = $bind;

        return $this;
    }

    public function setValue(mixed $bind_value): self
    {
        $this->bind_value = $bind_value;

        return $this;
    }

    public function setColumnName(string $column_name): self
    {
        $this->column_name = $column_name;

        return $this;
    }

    public function getBind(): string
    {
        return $this->prefix_bind . $this->bind;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->bind_value;
    }

    public function getColumnName(): string
    {
        return $this->column_name;
    }

    public function hasColumName(): bool
    {
        return '' !== $this->column_name;
    }

    public function markAsColumn(): self
    {
        $this->column_name = $this->bind;

        return $this;
    }

    public function hasBind(): bool
    {
        return '' === $this->bind;
    }
}
