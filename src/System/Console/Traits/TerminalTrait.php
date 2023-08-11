<?php

declare(strict_types=1);

namespace System\Console\Traits;

trait TerminalTrait
{
    /**
     * Get terminal width.
     */
    protected function getWidth(int $min = 80, int $max = 160): int
    {
        if (array_key_exists('COLUMNS', $_ENV)) {
            return $this->minMax((int) trim((string) $_ENV['COLUMNS']), $min, $max);
        }

        if (!function_exists('shell_exec')) {
            return $min;
        }

        if ('Windows' === PHP_OS_FAMILY) {
            $modeOutput = shell_exec('mode con');
            if (preg_match('/Columns:\s+(\d+)/', $modeOutput, $matches)) {
                return $this->minMax((int) $matches[1], $min, $max);
            }

            return $min;
        }

        $sttyOutput = shell_exec('stty size 2>&1');
        if ($sttyOutput) {
            $dimensions = explode(' ', trim($sttyOutput));
            if (2 === count($dimensions)) {
                return $this->minMax((int) $dimensions[1], $min, $max);
            }
        }

        return $min;
    }

    /**
     * Helper to get between min-max value.
     */
    private function minMax(int $value, int $min, int $max): int
    {
        return $value < $min ? $min : ($value > $max ? $max : $value);
    }
}
