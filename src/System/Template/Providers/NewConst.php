<?php

namespace System\Template\Providers;

use System\Template\Constant;

class NewConst
{
    public static function name(string $name)
    {
        return new Constant($name);
    }
}
