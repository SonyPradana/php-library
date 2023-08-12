<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Color;
use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Console\Style\Decorate;

trait CommandTrait
{
    use PrinterTrait;

    /** code (bash): 31 */
    protected function textRed(string $text): string
    {
        return $this->rule(Decorate::TEXT_RED, $text);
    }

    /** code (bash): 33 */
    protected function textYellow(string $text): string
    {
        return $this->rule(Decorate::TEXT_YELLOW, $text);
    }

    /** code (bash): 32 */
    protected function textGreen(string $text): string
    {
        return $this->rule(Decorate::TEXT_GREEN, $text);
    }

    /** code (bash): 34 */
    protected function textBlue(string $text): string
    {
        return $this->rule(Decorate::TEXT_BLUE, $text);
    }

    /** code (bash): 2 */
    protected function textDim(string $text): string
    {
        return $this->rule(Decorate::TEXT_DIM, $text);
    }

    /** code (bash): 35 */
    protected function textMagenta(string $text): string
    {
        return $this->rule(Decorate::TEXT_MAGENTA, $text);
    }

    /** code (bash): 36 */
    protected function textCyan(string $text): string
    {
        return $this->rule(Decorate::TEXT_CYAN, $text);
    }

    /** code (bash): 37 */
    protected function textLightGray(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_GRAY, $text);
    }

    /** code (bash): 90 */
    protected function textDarkGray(string $text): string
    {
        return $this->rule(Decorate::TEXT_DARK_GRAY, $text);
    }

    /** code (bash): 91 */
    protected function textLightRed(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_RED, $text);
    }

    /** code (bash): 92 */
    protected function textLightGreen(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_GREEN, $text);
    }

    /** code (bash): 93 */
    protected function textLightYellow(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_YELLOW, $text);
    }

    /** code (bash): 94 */
    protected function textLightBlue(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_BLUE, $text);
    }

    /** code (bash): 95 */
    protected function textLightMagenta(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_MAGENTA, $text);
    }

    /** code (bash): 96 */
    protected function textLightCyan(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_CYAN, $text);
    }

    /** code (bash): 97 */
    protected function textWhite(string $text): string
    {
        return $this->rule(Decorate::TEXT_WHITE, $text);
    }

    /** code (bash): 41 */
    protected function bgRed(string $text): string
    {
        return $this->rule(Decorate::BG_RED, $text);
    }

    /** code (bash): 43 */
    protected function bgYellow(string $text): string
    {
        return $this->rule(Decorate::BG_YELLOW, $text);
    }

    /** code (bash): 42 */
    protected function bgGreen(string $text): string
    {
        return $this->rule(Decorate::BG_GREEN, $text);
    }

    /** code (bash): 44 */
    protected function bgBlue(string $text): string
    {
        return $this->rule(Decorate::BG_BLUE, $text);
    }

    /** code (bash): 45 */
    protected function bgMagenta(string $text): string
    {
        return $this->rule(Decorate::BG_MAGENTA, $text);
    }

    /** code (bash): 46 */
    protected function bgCyan(string $text): string
    {
        return $this->rule(Decorate::BG_CYAN, $text);
    }

    /** code (bash): 47 */
    protected function bgLightGray(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_GRAY, $text);
    }

    /** code (bash): 100 */
    protected function bgDarkGray(string $text): string
    {
        return $this->rule(Decorate::BG_DARK_GRAY, $text);
    }

    /** code (bash): 101 */
    protected function bgLightRed(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_RED, $text);
    }

    /** code (bash): 102 */
    protected function bgLightGreen(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_GREEN, $text);
    }

    /** code (bash): 103 */
    protected function bgLightYellow(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_YELLOW, $text);
    }

    /** code (bash): 104 */
    protected function bgLightBlue(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_BLUE, $text);
    }

    /** code (bash): 105 */
    protected function bgLightMagenta(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_MAGENTA, $text);
    }

    /** code (bash): 106 */
    protected function bgLightCyan(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_CYAN, $text);
    }

    /** code (bash): 107 */
    protected function bgWhite(string $text): string
    {
        return $this->rule(Decorate::BG_WHITE, $text);
    }

    // JIT color

    /**
     * Just in time text color.
     *
     * @param ForegroundColor $color Color code 0-256
     */
    protected function textColor(ForegroundColor $color, string $text): string
    {
        return $this->rules($color->get(), $text);
    }

    /**
     * Just in time background color.
     *
     * @param BackgroundColor $color Color code 0-256
     */
    protected function bgColor(BackgroundColor $color, string $text): string
    {
        return $this->rules($color->get(), $text);
    }

    protected function textBold(string $text): string
    {
        return $this->rule(Decorate::BOLD, $text, true, Decorate::RESET_BOLD);
    }

    protected function textUnderline(string $text): string
    {
        return $this->rule(Decorate::UNDERLINE, $text, true, Decorate::RESET_UNDERLINE);
    }

    protected function textBlink(string $text): string
    {
        return $this->rule(Decorate::BLINK, $text, true, Decorate::RESET_BLINK);
    }

    protected function textReverse(string $text): string
    {
        return $this->rule(Decorate::REVERSE, $text, true, Decorate::RESET_REVERSE);
    }

    protected function textHidden(string $text): string
    {
        return $this->rule(Decorate::HIDDEN, $text, true, Decorate::RESET_HIDDEN);
    }
}
