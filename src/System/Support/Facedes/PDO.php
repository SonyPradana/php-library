<?php

declare(strict_types=1);

namespace System\Support\Facedes;

use System\Support\Facedes\Facede;

/**
 *  @method static \System\Database\MyPDO instance()
 */
final class PDO extends Facede
{
    protected static function getAccessor()
    {
        return \System\Database\MyPDO::class;
    }
}
