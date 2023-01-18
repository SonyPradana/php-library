<?php

declare(strict_types=1);

namespace System\Database\MySchema\Traits;

trait ConditionTrait
{
    /** @var string */
    private $if_exists = '';

    public function ifExists(bool $value = true): self
    {
        $this->if_exists = $value
            ? 'IF EXISTS'
            : 'IF NOT EXISTS'
        ;

        return $this;
    }

    public function ifNotExists(bool $value = true): self
    {
        $this->if_exists = $value
            ? 'IF NOT EXISTS'
            : 'IF EXISTS'
        ;

        return $this;
    }
}
