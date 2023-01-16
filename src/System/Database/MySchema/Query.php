<?php

declare(strict_types=1);

namespace System\Database\MySchema;

abstract class Query
{
    /** @var MyPDO PDO property */
    protected $pdo;

    /** @var string Main query */
    protected $query;

    public function __toString()
    {
        return $this->builder();
    }

    public function query(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    protected function builder(): string
    {
        return $this->query = '';
    }

    public function execute(): bool
    {
        return $this->pdo->query($this->query)->execute();
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
