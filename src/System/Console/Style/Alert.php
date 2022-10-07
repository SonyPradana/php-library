<?php

declare(strict_types=1);

namespace System\Console\Style;

use System\Console\Traits\AlertTrait;

class Alert
{
    use AlertTrait;

    /**
     * New instance.
     * 
     * @return Style
     */
    public static function render()
    {
        return new static;
    }
}
