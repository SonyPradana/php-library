<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static string                               __invoke(string ...$entry_ponits)
 * @method static \System\Integrate\Vite               manifestName(string $manifest_name)
 * @method static string                               manifest()
 * @method static array<string, array<string, string>> loader()
 * @method static string                               getManifest(string $resource_name)
 * @method static array<string, string>                getsManifest(string[] $resource_names)
 * @method static string                               get(string $resource_name)
 * @method static array<string, string>                gets(string[] $resource_names)
 * @method static bool                                 isRunningHRM()
 * @method static string                               getHmrUrl()
 * @method static string                               getHmrScript()
 * @method static int                                  cacheTime()
 * @method static int                                  manifestTime()
 */
final class Vite extends Facade
{
    protected static function getAccessor()
    {
        return 'vite.get';
    }
}
