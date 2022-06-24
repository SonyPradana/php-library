<?php

declare(strict_types=1);

namespace System\Support\Facedes;

use System\Support\Facedes\Facede;

/**
 *  @method static \System\Database\MyQuery\Table table(string $from)
 */
final class DB extends Facede
{
    protected static function getAccessor()
    {
        return 'MyQuery';
    }
}
