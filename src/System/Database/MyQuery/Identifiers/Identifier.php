<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Identifiers;

use System\Database\Interfaces\EscapeQuery;

class Identifier implements EscapeQuery
{
    public function escape(?string $identifier): ?string
    {
        return $identifier;
    }
}
