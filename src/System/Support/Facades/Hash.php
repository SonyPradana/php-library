<?php

declare(strict_types=1);

namespace System\Support\Facades;

use System\Security\Hashing\HashManager;

/**
 * @method static self                                   setDefaultDriver(\System\Security\Hashing\HashInterface $driver)
 * @method static self                                   setDriver(string $driver_name, \System\Security\Hashing\HashInterface $driver)
 * @method static \System\Security\Hashing\HashInterface driver(?string $driver = null)
 * @method static array                                  info(string $hashed_value)
 * @method static string                                 make(string $value, array $options = [])
 * @method static bool                                   verify(string $value, string $hashed_value, array $options = [])
 * @method static bool                                   isValidAlgorithm(string $hash)
 */
final class Hash extends Facade
{
    protected static function getAccessor()
    {
        return HashManager::class;
    }
}
