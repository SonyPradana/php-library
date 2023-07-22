<?php

declare(strict_types=1);

namespace System\Integrate;

use System\Collection\Collection;
use System\Text\Str;

class Vite
{
    private string $public_path;
    private string $build_path;
    private string $manifest_name;
    private int $cache_time = 0;
    /** @var array<string, array<string, array<string, string>>> */
    public static array $cache = [];
    public static ?string $hot = null;

    public function __construct(string $public_path, string $build_path)
    {
        $this->public_path          = $public_path;
        $this->build_path           = $build_path;
        $this->manifest_name        = 'manifest.json';
    }

    /**
     * Get resource using entri ponit(s).
     *
     * @param string $entry_ponits
     *
     * @return array<string, string>|string
     *                                      If entry point is string will return string,
     *                                      otherwise if entry point is array return as array
     */
    public function __invoke(...$entry_ponits)
    {
        $resource = $this->gets($entry_ponits);
        $first    = array_key_first($resource);

        return 1 === count($resource) ? $resource[$first] : $resource;
    }

    public function manifestName(string $manifest_name): self
    {
        $this->manifest_name = $manifest_name;

        return $this;
    }

    public static function flush(): void
    {
        static::$cache = [];
        static::$hot   = null;
    }

    /**
     * Get menifest filename.
     */
    public function manifest(): string
    {
        if (file_exists($file_name = "{$this->public_path}/{$this->build_path}/{$this->manifest_name}")) {
            return $file_name;
        }

        throw new \Exception("Manifest file not found {$file_name}");
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function loader(): array
    {
        $file_name = $this->manifest();

        if (array_key_exists($file_name, static::$cache)) {
            return static::$cache[$file_name];
        }

        $this->cache_time = $this->manifestTime();
        $load             = file_get_contents($file_name);
        $json             = json_decode($load, true);

        if (false === $json) {
            throw new \Exception('Manifest doest support');
        }

        return static::$cache[$file_name] = $json;
    }

    public function getManifest(string $resource_name): string
    {
        $asset = $this->loader();

        if (!array_key_exists($resource_name, $asset)) {
            throw new \Exception("Resoure file not found {$resource_name}");
        }

        return $this->build_path . $asset[$resource_name]['file'];
    }

    /**
     * @param string[] $resource_names
     *
     * @return array<string, string>
     */
    public function getsManifest($resource_names)
    {
        $asset = $this->loader();

        $resources = [];
        foreach ($resource_names as $resource) {
            if (array_key_exists($resource, $asset)) {
                $resources[$resource] = $this->build_path . $asset[$resource]['file'];
            }
        }

        return $resources;
    }

    /**
     * Get hot url (if hot not found will return with manifest).
     */
    public function get(string $resource_name): string
    {
        if (!$this->isRunningHRM()) {
            return $this->getManifest($resource_name);
        }

        $hot = $this->getHmrUrl();

        return $hot . $resource_name;
    }

    /**
     * Get hot url (if hot not found will return with manifest).
     *
     * @param string[] $resource_names
     *
     * @return array<string, string>
     */
    public function gets($resource_names)
    {
        if (!$this->isRunningHRM()) {
            return $this->getsManifest($resource_names);
        }

        $hot  = $this->getHmrUrl();

        return (new Collection($resource_names))
            ->assocBy(fn ($asset) => [$asset => $hot . $asset])
            ->toArray()
        ;
    }

    /**
     * Determine if the HMR server is running.
     */
    public function isRunningHRM(): bool
    {
        return is_file("{$this->public_path}/hot");
    }

    /**
     * Get hot url.
     */
    public function getHmrUrl(): string
    {
        if (!is_null(static::$hot)) {
            return static::$hot;
        }

        $hot  = file_get_contents("{$this->public_path}/hot");
        $hot  = rtrim($hot);
        $dash = Str::endsWith($hot, '/') ? '' : '/';

        return static::$hot = $hot . $dash;
    }

    public function getHmrScript(): string
    {
        return '<script type="module" src="' . $this->getHmrUrl() . '@vite/client"></script>';
    }

    public function cacheTime(): int
    {
        return $this->cache_time;
    }

    public function manifestTime(): int
    {
        return filemtime($this->manifest());
    }
}
