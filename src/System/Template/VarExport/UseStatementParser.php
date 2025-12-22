<?php

declare(strict_types=1);

namespace System\Template\VarExport;

use function System\Console\ok;
use function System\Console\style;

final class UseStatementParser
{
    /**
     * @return array<int, string>
     */
    public function parse(string $file): array
    {
        if (false === is_file($file)) {
            return [];
        }

        $tokens = token_get_all((string) file_get_contents($file));
        $uses = [];

        $i = 0;
        $count = count($tokens);

        while ($i < $count) {
            $token = $tokens[$i];

            if (true === is_array($token) && T_USE === $token[0]) {
                $i++;
                $this->parseUseStatement($tokens, $i, $uses);
            }

            $i++;
        }

        return array_values(array_unique($uses));
    }

    /**
     * @param array<int, mixed> $tokens
     * @param int $i
     * @param array<int, string> $uses
     */
    private function parseUseStatement(array $tokens, int &$i, array &$uses): void {
        $base = '';

        while (isset($tokens[$i])) {
            $token = $tokens[$i];

            if (true === is_array($token)) {
                if (T_STRING === $token[0] || T_NS_SEPARATOR === $token[0] || T_NAME_QUALIFIED === $token[0]) {
                    $base .= $token[1];
                }

                if (T_AS === $token[0]) {
                    // skip alias
                    $i++;
                    while (isset($tokens[$i]) && true === is_array($tokens[$i])) {
                        if (T_STRING === $tokens[$i][0]) {
                            break;
                        }
                        $i++;
                    }
                }
            } else {
                if ('{' === $token) {
                    $this->parseGroupedUse($tokens, $i, $base, $uses);
                    return;
                }

                if (',' === $token) {
                    $uses[] = $base;
                    $base = '';
                }

                if (';' === $token) {
                    $uses[] = $base;
                    return;
                }
            }

            $i++;
        }
    }

    /**
     * @param array<int, mixed> $tokens
     * @param array<int, string> $uses
     */
    private function parseGroupedUse(array $tokens, int &$i, string $base, array &$uses): void
    {
        $i++;

        while (isset($tokens[$i])) {
            $class = '';

            while (isset($tokens[$i])) {
                $token = $tokens[$i];

                if (true === is_array($token)) {
                    if (T_STRING === $token[0] || T_NS_SEPARATOR === $token[0] || T_NAME_QUALIFIED === $token[0]) {
                        $class .= $token[1];
                    }
                } else {
                    if (',' === $token || '}' === $token) {
                        if ('' !== $class) {
                            $uses[] = $base . '\\' . $class;
                        }
                        break;
                    }
                }

                $i++;
            }

            if ('}' === $token) {
                while (isset($tokens[$i]) && ';' !== $tokens[$i]) {
                    $i++;
                }
                return;
            }

            $i++;
        }
    }
}

