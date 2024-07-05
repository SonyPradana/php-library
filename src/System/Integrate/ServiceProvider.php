<?php

declare(strict_types=1);

namespace System\Integrate;

abstract class ServiceProvider
{
    /** @var Application */
    protected $app;

    /** @var array<int|string, class-string> Class register */
    protected $register = [
        // register
    ];

    /**
     * Shared modules to import from vendor package.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $modules = [];

    /**
     * Create a new service provider instance.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Boot provider.
     *
     * @return void
     */
    public function boot()
    {
        // boot
    }

    /**
     * Register to application container before booted.
     *
     * @return void
     */
    public function register()
    {
        // register application container
    }

    /**
     * Import a specific file to the application.
     */
    public static function importFile(string $from, string $to, bool $overwrite = false): bool
    {
        $exists = file_exists($to);
        if (($exists && $overwrite) || false === $exists) {
            $path = pathinfo($to, PATHINFO_DIRNAME);
            if (false === file_exists($path)) {
                mkdir($path, 0755, true);
            }

            return copy($from, $to);
        }

        return false;
    }

    /**
     * Import a directory to the application.
     */
    public static function importDir(string $from, string $to, bool $overwrite = false): bool
    {
        $dir = opendir($from);
        if (false === $dir) {
            return false;
        }

        if (false === file_exists($to)) {
            mkdir($to, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $src = $from . '/' . $file;
            $dst = $to . '/' . $file;

            if (is_dir($src)) {
                if (false === static::importDir($src, $dst, $overwrite)) {
                    closedir($dir);

                    return false;
                }
            } else {
                if (false === static::importFile($src, $dst, $overwrite)) {
                    closedir($dir);

                    return false;
                }
            }
        }

        closedir($dir);

        return true;
    }

    /**
     * Register a package to the module.
     *
     * @param array<string, string> $path
     */
    public static function export(array $path, string $tag = ''): void
    {
        if (false === array_key_exists($tag, static::$modules)) {
            static::$modules[$tag] = [];
        }

        static::$modules[$tag] = array_merge(static::$modules[$tag], $path);
    }

    /**
     * Get registers modules.
     *
     * @return array<string, array<string, string>>
     */
    public static function getModules(): array
    {
        return static::$modules;
    }

    /**
     * Flush shared modules.
     */
    public static function flushModule(): void
    {
        static::$modules = [];
    }
}
