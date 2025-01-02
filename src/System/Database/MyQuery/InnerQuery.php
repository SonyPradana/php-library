<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

final class InnerQuery implements \Stringable
{
    public function __construct(private ?Select $select = null, private string $table = '')
    {
    }

    public function isSubQuery(): bool
    {
        return null !== $this->select;
    }

    public function getAlias(): string
    {
        return $this->table;
    }

    /** @return Bind[]  */
    public function getBind(): array
    {
        return $this->select->getBinds();
    }

    public function __toString(): string
    {
        return $this->isSubQuery()
            ? '(' . trim((string) $this->select) . ') AS ' . $this->getAlias()
            : $this->getAlias();
    }
}
