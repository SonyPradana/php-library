<?php

use System\Support\Facedes\Facede;

/**
 * @method static \System\Time\Now year(int $year)
 * @method static bool isNextYear()
 */
final class FacedesTestClass extends Facede
{
    protected static function setAccessor()
    {
        static::$accessor = static::$app->get(System\Time\Now::class);
    }
}
