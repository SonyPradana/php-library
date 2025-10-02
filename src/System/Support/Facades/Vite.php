<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Integrate\Vite                        manifestName(string $manifest_name)
 * @method static void                                          flush()
 * @method static string                                        manifest()
 * @method static array<string, array<string, string|string[]>> loader()
 * @method static string                                        getManifest(string $resource_name)
 * @method static array<string, string>                         getsManifest(string[] $resource_names)
 * @method static array{imports: string[], css: string[]}       getManifestImports(string[] $resources)
 * @method static string                                        get(string $resource_name)
 * @method static array<string, string>                         gets(string[] $resource_names)
 * @method static bool                                          isRunningHRM()
 * @method static string                                        getHmrUrl()
 * @method static string                                        getHmrScript()
 * @method static int                                           cacheTime()
 * @method static int                                           manifestTime()
 * @method static string                                        getPreloadTags(string[] $entrypoints)
 * @method static string                                        getTags(string[] $entrypoints, array<string|int, string|bool|int|null> $attributes = null)
 * @method static string                                        getCostumeTags(array<string, array<string|int, string|bool|int|null>> $entrypoints, array<string|int, string|bool|int|null> $default_attributes = [])
 *
 * @see System\Integrate\Vite
 */
final class Vite extends Facade
{
    protected static function getAccessor()
    {
        return 'vite.gets';
    }
}
