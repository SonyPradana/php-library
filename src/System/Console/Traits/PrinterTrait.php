<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Decorate;

trait PrinterTrait
{
    /**
     * Run commandline text rule.
     *
     * @param array<int, string|int> $rule
     * @param array<int, string|int> $reset_rule
     */
    protected function rules(array $rule, string $text, bool $reset = true, array $reset_rule = [Decorate::RESET]): string
    {
        $string_rules       = implode(';', $rule);
        $string_reset_rules = implode(';', $reset_rule);

        return $this->rule($string_rules, $text, $reset, $string_reset_rules);
    }

    /**
     * Run color code.
     *
     * @param int|string $rule
     * @param string     $text
     * @param bool       $reset
     * @param int|string $reset_rule
     *
     * @return string
     */
    protected function rule($rule, $text, $reset = true, $reset_rule = Decorate::RESET)
    {
        $rule       = "\e[" . $rule . 'm' . $text;
        $reset_rule = "\e[" . $reset_rule . 'm';

        return $reset
            ? $rule . $reset_rule
            : $rule;
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
     * Clear from the cursor position to the beginning of the line.
     *
     * @return void
     */
    protected function clear_cursor()
    {
        echo "\e[1K";
    }

    /**
     * Clear everything on the line.
     *
     * @return void
     */
    protected function clear_line()
    {
        echo "\e[2K";
    }
}
