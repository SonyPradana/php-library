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

    /**
     * Reference: Composer\XdebugHandler\Process::supportsColor
     * https://github.com/composer/xdebug-handler.
     *
     * the origina code
     *
     * @source https://github.com/symfony/console/blob/6.4/Output%2FStreamOutput.php#L80-L124
     *
     * @param resource $stream
     */
    protected function hasColorSupport($stream = \STDOUT): bool
    {
        if ('' !== (($_SERVER['NO_COLOR'] ?? getenv('NO_COLOR'))[0] ?? '')) {
            return false;
        }

        if (!@stream_isatty($stream) && !\in_array(strtoupper((string) getenv('MSYSTEM')), ['MINGW32', 'MINGW64'], true)) {
            return false;
        }

        if ('\\' === \DIRECTORY_SEPARATOR && @sapi_windows_vt100_support($stream)) {
            return true;
        }

        if ('Hyper' === getenv('TERM_PROGRAM')
            || false !== getenv('COLORTERM')
            || false !== getenv('ANSICON')
            || 'ON' === getenv('ConEmuANSI')
        ) {
            return true;
        }

        if ('dumb' === $term = (string) getenv('TERM')) {
            return false;
        }

        return 1 === preg_match('/^((screen|xterm|vt100|vt220|putty|rxvt|ansi|cygwin|linux).*)|(.*-256(color)?(-bce)?)$/', $term);
    }
}
