<?php

declare(strict_types=1);

namespace System\Template\Providers;

use System\Template\Property;

class NewProperty
{
    public static function name(string $name): Property
    {
        return new Property($name);
    }
}
