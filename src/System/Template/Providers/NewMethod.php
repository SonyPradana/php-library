<?php

namespace System\Template\Providers;

use System\Template\Method;

class NewMethod
{
    public static function name(string $name)
    {
        return new Method($name);
    }
}
