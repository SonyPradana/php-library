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
    protected $unsigned;
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
        $this->unsigned       = '';
    }

    public function __toString()
    {
        return $this->query();
    }

    private function query(): string
    {
        $collumn = [
            $this->data_type,
            $this->unsigned,
            $this->null_able,
            $this->default,
            $this->auto_increment,
            $this->raw,
            $this->order,
        ];

        return implode(' ', array_filter($collumn, fn ($item) => $item !== ''));
    }

    public function notNull(bool $notNull = true): self
    {
        $this->null_able = $notNull ? 'NOT NULL' : 'NULL';

        return $this;
    }

    public function null(bool $null = true): self
    {
        return $this->notNull(!$null);
    }

    /**
     * Set default constraint.
     *
     * @param string|int $default Default set value
     * @param bool       $wrap    Wrap default value with "'"
     */
    public function default($default, bool $wrap = true): self
    {
        $wrap          = is_int($default) ? false : $wrap;
        $this->default = $wrap ? "DEFAULT '{$default}'" : "DEFAULT {$default}";

        return $this;
    }

    public function defaultNull(): self
    {
        return $this->default('NULL', false);
    }

    public function autoIncrement(bool $incremnet = true): self
    {
        $this->auto_increment = $incremnet ? 'AUTO_INCREMENT' : '';

        return $this;
    }

    public function increment(bool $incremnet): self
    {
        return $this->autoIncrement($incremnet);
    }

    /**
     * Make datatype tobe unsigned (int, tinyint, bigint, smallint).
     */
    public function unsigned(): self
    {
        if (false === preg_match('/^(int|tinyint|bigint|smallint)(\(\d+\))?$/', $this->data_type)) {
            throw new \Exception('Cant use UNSIGNED not integer datatype.');
        }
        $this->unsigned = 'UNSIGNED';

        return $this;
    }

    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
