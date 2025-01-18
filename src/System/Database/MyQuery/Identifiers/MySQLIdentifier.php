<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Identifiers;

class MySQLIdentifier extends Identifier
{
    public function escape(?string $identifier): ?string
    {
        if (null === $identifier) {
            return null;
        }

        $parts = explode('.', str_replace('`', '', $identifier));

        if (1 === count($parts)) {
            return '`' . $parts[0] . '`';
        }

        return '`' . implode('`.`', $parts) . '`';
    }
}
