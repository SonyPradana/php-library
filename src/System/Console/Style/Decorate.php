<?php

declare(strict_types=1);

namespace System\Console\Style;

class Decorate
{
    // text
    public const TEXT_DIM           = 2;
    public const TEXT_RED           = 31;
    public const TEXT_GREEN         = 32;
    public const TEXT_YELLOW        = 33;
    public const TEXT_BLUE          = 34;
    public const TEXT_MAGENTA       = 35;
    public const TEXT_CYAN          = 36;
    public const TEXT_LIGHT_GRAY    = 37;
    public const TEXT_DEFAULT       = 39;
    public const TEXT_DARK_GRAY     = 90;
    public const TEXT_LIGHT_RED     = 91;
    public const TEXT_LIGHT_GREEN   = 92;
    public const TEXT_LIGHT_YELLOW  = 93;
    public const TEXT_LIGHT_BLUE    = 94;
    public const TEXT_LIGHT_MAGENTA = 95;
    public const TEXT_LIGHT_CYAN    = 96;
    public const TEXT_WHITE         = 97;
    // background color
    public const BG_RED           = 41;
    public const BG_GREEN         = 42;
    public const BG_YELLOW        = 43;
    public const BG_BLUE          = 44;
    public const BG_MAGENTA       = 45;
    public const BG_CYAN          = 46;
    public const BG_LIGHT_GRAY    = 47;
    public const BG_DEFAULT       = 49;
    public const BG_DARK_GRAY     = 100;
    public const BG_LIGHT_RED     = 101;
    public const BG_LIGHT_GREEN   = 102;
    public const BG_LIGHT_YELLOW  = 103;
    public const BG_LIGHT_BLUE    = 104;
    public const BG_LIGHT_MAGENTA = 105;
    public const BG_LIGHT_CYAN    = 106;
    public const BG_WHITE         = 107;
    // other
    public const BOLD            = 1;
    public const UNDERLINE       = 4;
    public const BLINK           = 5;
    public const REVERSE         = 7;
    public const HIDDEN          = 8;
    // reset
    public const RESET           = 0;
    public const RESET_BOLD      = 21;
    public const RESET_BOLD_DIM  = 22;
    public const RESET_UNDERLINE = 24;
    public const RESET_BLINK     = 25;
    public const RESET_REVERSE   = 27;
    public const RESET_HIDDEN    = 28;
    // more code see https://misc.flogisoft.com/bash/tip_colors_and_formatting

    /**
     * Get contant from string.
     *
     * @param string $name
     *
     * @return int
     */
    public static function getConst($name)
    {
        return constant("self::{$name}");
    }
}
