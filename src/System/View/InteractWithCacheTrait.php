<?php

declare(strict_types=1);

namespace System\View;

trait InteractWithCacheTrait
{
    /**
     * Get contents using cache first.
     */
    private function getContents(string $file_name): string
    {
        if (false === array_key_exists($file_name, self::$cache)) {
            self::$cache[$file_name] = file_get_contents($file_name);
        }

        return self::$cache[$file_name];
    }
}
