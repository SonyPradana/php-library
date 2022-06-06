<?php

namespace System\Template\Providers;

use System\Template\Property;

class NewProperty
{
    public static function name(string $name)
    {
        return new Property($name);
    }
}
