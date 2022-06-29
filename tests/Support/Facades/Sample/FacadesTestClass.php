<?php

use System\Support\Facades\Facade;

/**
 * @method static \System\Time\Now year(int $year)
 * @method static bool isNextYear()
 */
final class FacadesTestClass extends Facade
{
    protected static function getAccessor()
    {
        return System\Time\Now::class;
    }
}
