<?php

declare(strict_types=1);

namespace System\Template\Providers;

use System\Template\Constant;

class NewConst
{
    public static function name(string $name): Constant
    {
        return new Constant($name);
    }
}
