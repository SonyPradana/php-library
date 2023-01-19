<?php

declare(strict_types=1);

namespace System\Database\MySchema;

abstract class Query
{
    /** @var MyPDO PDO property */
    protected $pdo;

    public function __toString()
    {
        return $this->builder();
    }

    protected function builder(): string
    {
        return '';
    }

    public function execute(): bool
    {
        return $this->pdo->query($this->builder())->execute();
    }

    /**
     * Helper: join condition into string.
     *
     * @param string[] $array
     */
    protected function join(array $array, string $sperator = ' '): string
    {
        return implode(
            $sperator,
            array_filter($array, fn ($item) => $item !== '')
        );
    }
}
