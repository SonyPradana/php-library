<?php

declare(strict_types=1);

namespace System\Support\Facades;

use System\Integrate\ConfigRepository;

/**
 * @method static bool                 has(string $key)
 * @method static mixed                get(string $key, mixed $default = null)
 * @method static void                 set(string $key, mixed $value)
 * @method static void                 push(string $key, mixed $value)
 * @method static array<string, mixed> toArray()
 */
final class Config extends Facade
{
    protected static function getAccessor()
    {
        return ConfigRepository::class;
    }
}
