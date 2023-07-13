<?php

declare(strict_types=1);

namespace System\Template\Providers;

use System\Template\Method;

class NewMethod
{
    public static function name(string $name): Method
    {
        return new Method($name);
    }
}
