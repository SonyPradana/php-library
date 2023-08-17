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
     * @param string|int             $text
     * @param array<int, string|int> $reset_rule
     */
    protected function rules(array $rule, $text, bool $reset = true, array $reset_rule = [Decorate::RESET]): string
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
        $rule       = chr(27) . '[' . $rule . 'm' . $text;
        $reset_rule = chr(27) . '[' . $reset_rule . 'm';

        return $reset
            ? $rule . $reset_rule
            : $rule;
    }

    /**
     * Print new line x times.
     *
     * @deprecated
     */
    protected function print_n(int $count = 1): void
    {
        echo str_repeat("\n", $count);
    }

    /**
     * Print tab x times.
     *
     * @deprecated
     */
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
     * @deprecated
     *
     * @return void
     */
    protected function clear_cursor()
    {
        echo chr(27) . '[1K';
    }

    /**
     * Clear everything on the line.
     *
     * @deprecated
     *
     * @return void
     */
    protected function clear_line()
    {
        echo chr(27) . '[2K';
    }

    /**
     * Replace single line output to new string.
     */
    protected function replaceLine(string $replace, int $line = -1): void
    {
        $this->moveLine($line);
        echo chr(27) . "[K\r" . $replace;
    }

    /**
     * Remove / reset curent line to empty.
     */
    protected function clearLine(int $line = -1): void
    {
        $this->moveLine($line);
        $this->replaceLine('');
    }

    /**
     * Move to line (start from bottom).
     */
    protected function moveLine(int $line): void
    {
        echo chr(27) . "[{$line}A";
    }
}
