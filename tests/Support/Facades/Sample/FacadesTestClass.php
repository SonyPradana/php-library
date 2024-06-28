<?php

use System\Collection\Collection;
use System\Support\Facades\Facade;

/**
 * @method static bool has(string $key)
 */
final class FacadesTestClass extends Facade
{
    protected static function getAccessor()
    {
        return Collection::class;
    }
}
