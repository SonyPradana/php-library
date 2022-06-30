<?php

namespace System\Console;

trait TraitCommand
{
    /**
     * Run commandline text rule.
     *
     * @param array<int, string> $rule
     */
    protected function rules(array $rule, string $text, bool $reset_rule = true): string
    {
        $toString = implode(';', $rule);
        $string   = "\e[$toString" . "m$text";

        return $reset_rule ? $string . "\e[0m" : $string;
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
        echo "\033[1K";
    }

    /** code (bash): 31 */
    protected function textRed(string $text): string
    {
        return "\e[31m$text\e[0m";
    }

    /** code (bash): 33 */
    protected function textYellow(string $text): string
    {
        return "\e[33m$text\e[0m";
    }

    /** code (bash): 32 */
    protected function textGreen(string $text): string
    {
        return "\e[32m$text\e[0m";
    }

    /** code (bash): 34 */
    protected function textBlue(string $text): string
    {
        return "\e[34m$text\e[0m";
    }

    /** code (bash): 2 */
    protected function textDim(string $text): string
    {
        return "\e[2m$text\e[0m";
    }

    /** code (bash): 97 */
    protected function textWhite(string $text): string
    {
        return "\e[97m$text\e[0m";
    }

    /** code (bash): 41 */
    protected function bgRed(string $text): string
    {
        return "\e[41m$text\e[0m";
    }

    /** code (bash): 43 */
    protected function bgYellow(string $text): string
    {
        return "\e[43m$text\e[0m";
    }

    /** code (bash): 42 */
    protected function bgGreen(string $text): string
    {
        return "\e[42m$text\e[0m";
    }

    /** code (bash): 44 */
    protected function bgBlue(string $text): string
    {
        return "\e[44m$text\e[0m";
    }

    /** code (bash): 107 */
    protected function bgWhite(string $text): string
    {
        return "\e[107m$text\e[0m";
    }
}
