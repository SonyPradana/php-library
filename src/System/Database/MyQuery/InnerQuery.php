<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\Interfaces\EscapeQuery;
use System\Database\MyQuery\Identifiers\MySQLIdentifier;

final class InnerQuery implements \Stringable, EscapeQuery
{
    public function __construct(
        private ?Select $select = null,
        private string $table = '',
        private ?EscapeQuery $escapeQuery = null,
    ) {
    }

    public function escape(?string $identifier): ?string
    {
        if (null === $this->escapeQuery) {
            $this->escapeQuery = new MySQLIdentifier();
        }

        return $this->escapeQuery->escape($identifier);
    }

    public function setEscape(?EscapeQuery $escapeQuery): self
    {
        $this->escapeQuery = $escapeQuery;

        return $this;
    }

    public function isSubQuery(): bool
    {
        return null !== $this->select;
    }

    public function getAlias(): string
    {
        return $this->escape($this->table);
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
