<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Identifiers;

class MySQLIdentifier extends Identifier
{
    private static array $cache = [];

    public function escape(?string $identifier): ?string
    {
        if (null === $identifier) {
            return null;
        }

        if (array_key_exists($identifier, self::$cache)) {
            return self::$cache[$identifier];
        }

        $parts = explode('.', str_replace('`', '', $identifier));

        if (1 === count($parts)) {
            return '`' . $parts[0] . '`';
        }

        return self::$cache[$identifier] = '`' . implode('`.`', $parts) . '`';
    }
}
