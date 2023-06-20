<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Database\MyPDO instance()
 */
final class PDO extends Facade
{
    protected static function getAccessor()
    {
        return \System\Database\MyPDO::class;
    }
}
