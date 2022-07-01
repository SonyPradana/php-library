<?php

namespace System\Console;

trait TraitCommand
{
    /**
     * Run commandline text rule.
     *
     * @param array<int, string|int> $rule
     */
    protected function rules(array $rule, string $text, bool $reset_rule = true): string
    {
        $string_rules = implode(';', $rule);

        return $this->rule($string_rules, $text, $reset_rule);
    }

    /**
     * Run color code.
     *
     * @param int|string $rule
     * @param string     $text
     * @param bool       $reset
     *
     * @return string
     */
    protected function rule($rule, $text, $reset = true)
    {
        $rule = "\e[" . $rule . 'm' . $text;

        return $reset
            ? $rule . "\e[0m"
            : $rule;
    }

    /**
     * Echo array of string.
     *
     * @param array<int, string> $string
     */
    protected function prints(array $string): void
    {
        foreach ($string as $print) {
            echo $print;
        }
    }

    protected function print_n(int $count = 1): void
    {
        echo str_repeat("\n", $count);
    }

    protected function print_t(int $count = 1): void
    {
        echo str_repeat("\t", $count);
    }

    protected function newLine(int $count = 1): string
    {
        return str_repeat("\n", $count);
    }

    protected function tabs(int $count = 1): string
    {
        return str_repeat("\t", $count);
    }

    /**
     * Echo clear format commandline.
     *
     * @return void
     */
    protected function clear_line()
    {
        echo "\e[1K";
    }

    /** code (bash): 31 */
    protected function textRed(string $text): string
    {
        return $this->rule(Command::TEXT_RED, $text);
    }

    /** code (bash): 33 */
    protected function textYellow(string $text): string
    {
        return $this->rule(Command::TEXT_YELLOW, $text);
    }

    /** code (bash): 32 */
    protected function textGreen(string $text): string
    {
        return $this->rule(Command::TEXT_GREEN, $text);
    }

    /** code (bash): 34 */
    protected function textBlue(string $text): string
    {
        return $this->rule(Command::TEXT_BLUE, $text);
    }

    /** code (bash): 2 */
    protected function textDim(string $text): string
    {
        return $this->rule(Command::TEXT_DIM, $text);
    }

    /** code (bash): 35 */
    protected function textMageta(string $text): string
    {
        return $this->rule(Command::TEXT_MAGENTA, $text);
    }

    /** code (bash): 36 */
    protected function textCyan(string $text): string
    {
        return $this->rule(Command::TEXT_CYAN, $text);
    }

    /** code (bash): 37 */
    protected function textLightGray(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_GRAY, $text);
    }

    /** code (bash): 90 */
    protected function textDarkGray(string $text): string
    {
        return $this->rule(Command::TEXT_DARK_GRAY, $text);
    }

    /** code (bash): 91 */
    protected function textLightRed(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_RED, $text);
    }

    /** code (bash): 92 */
    protected function textLightGreen(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_GREEN, $text);
    }

    /** code (bash): 93 */
    protected function textLightYellow(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_YELLOW, $text);
    }

    /** code (bash): 94 */
    protected function textLightBlue(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_BLUE, $text);
    }

    /** code (bash): 95 */
    protected function textLightMagenta(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_MAGENTA, $text);
    }

    /** code (bash): 96 */
    protected function textLightCyan(string $text): string
    {
        return $this->rule(Command::TEXT_LIGHT_CYAN, $text);
    }

    /** code (bash): 97 */
    protected function textWhite(string $text): string
    {
        return $this->rule(Command::TEXT_WHITE, $text);
    }

    /** code (bash): 41 */
    protected function bgRed(string $text): string
    {
        return $this->rule(Command::BG_RED, $text);
    }

    /** code (bash): 43 */
    protected function bgYellow(string $text): string
    {
        return $this->rule(Command::BG_YELLOW, $text);
    }

    /** code (bash): 42 */
    protected function bgGreen(string $text): string
    {
        return $this->rule(Command::BG_GREEN, $text);
    }

    /** code (bash): 44 */
    protected function bgBlue(string $text): string
    {
        return $this->rule(Command::BG_BLUE, $text);
    }

    /** code (bash): 45 */
    protected function bgMagenta(string $text): string
    {
        return $this->rule(Command::BG_MAGENTA, $text);
    }

    /** code (bash): 46 */
    protected function bgCyan(string $text): string
    {
        return $this->rule(Command::BG_CYAN, $text);
    }

    /** code (bash): 47 */
    protected function bgLightGray(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_GRAY, $text);
    }

    /** code (bash): 100 */
    protected function bgDrakGray(string $text): string
    {
        return $this->rule(Command::BG_DARK_GRAY, $text);
    }

    /** code (bash): 101 */
    protected function bgLightRed(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_RED, $text);
    }

    /** code (bash): 102 */
    protected function bgLightGreen(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_GREEN, $text);
    }

    /** code (bash): 103 */
    protected function bgLightYellow(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_YELLOW, $text);
    }

    /** code (bash): 104 */
    protected function bgLightBlue(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_BLUE, $text);
    }

    /** code (bash): 105 */
    protected function bgLightMagenta(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_MAGENTA, $text);
    }

    /** code (bash): 106 */
    protected function bgLightCyan(string $text): string
    {
        return $this->rule(Command::BG_LIGHT_CYAN, $text);
    }

    /** code (bash): 107 */
    protected function bgWhite(string $text): string
    {
        return $this->rule(Command::BG_WHITE, $text);
    }

    // JIT color

    /**
     * Just in time text color.
     *
     * @param int $color Color code 0-256
     */
    protected function textColor(int $color, string $text): string
    {
        if ($color < 0 | $color > 256) {
            throw new \InvalidArgumentException('Color code must 0-256 range.');
        }

        return $this->rules([38, 5, $color], $text);
    }

    /**
     * Just in time bachgroud color.
     *
     * @param int $color Color code 0-256
     */
    protected function bgColor(int $color, string $text): string
    {
        if ($color < 0 | $color > 256) {
            throw new \InvalidArgumentException('Color code must 0-256 range.');
        }

        return $this->rules([48, 5, $color], $text);
    }

    protected function textBold(string $text): string
    {
        return "\e[1m" . $text . "\e[21m";
    }

    protected function textUnderline(string $text): string
    {
        return "\e[4m" . $text . "\e[24m";
    }

    protected function textBlink(string $text): string
    {
        return "\e[5m" . $text . "\e[25m";
    }

    protected function textReverse(string $text): string
    {
        return "\e[7m" . $text . "\e[27m";
    }

    protected function textHidden(string $text): string
    {
        return "\e[8m" . $text . "\e[28m";
    }
}
