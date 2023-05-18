<?php

declare(strict_types=1);

namespace System\Integrate;

use System\Collection\Collection;

class Vite
{
    private string $build_path;
    private string $manifest_name;
    private int $cache_time = 0;

    public function __construct(string $build_path, string $manifest_name)
    {
        $this->build_path          = $build_path;
        $this->manifest_name       = $manifest_name;
    }

    /**
     * Get menifest filename.
     */
    public function manifest(): string
    {
        if (file_exists($file_name = "{$this->build_path}/{$this->manifest_name}")) {
            return $file_name;
        }

        throw new \Exception("Manifest file not found {$file_name}");
    }

    public function loader(): array
    {
        $file_name = $this->manifest();

        $this->cache_time = $this->manifestTime();
        $load             = file_get_contents($file_name);
        $json             = json_decode($load, true);

        if (false === $json) {
            throw new \Exception('Manifest doest support');
        }

        return $json;
    }

    public function get(string $resource_name): string
    {
        $asset = $this->loader();

        if (!array_key_exists($resource_name, $asset)) {
            throw new \Exception("Resoure file not found {$resource_name}");
        }

        return $asset[$resource_name]['file'];
    }

    /**
     * @param string[] $resource_names
     *
     * @return array<string, string>
     */
    public function gets($resource_names)
    {
        $asset = $this->loader();

        $resources = [];
        foreach ($resource_names as $resource) {
            if (array_key_exists($resource, $asset)) {
                $resources[$resource] = $asset[$resource]['file'];
            }
        }

        return $resources;
    }

    /**
     * Get hot url (if hot not found will return with manifest).
     */
    public function getHotUrl(string $public_path, string $resource_name): string
    {
        if (!$this->isRunningHRM($public_path)) {
            return $this->get($resource_name);
        }

        $hot = file_get_contents("{$public_path}/hot");
        $hot = rtrim($hot);

        return $hot . $resource_name;
    }

    /**
     * Get hot url (if hot not found will return with manifest).
     *
     * @param string[] $resource_names
     *
     * @return array<string, string>
     */
    public function getsHotUrl(string $public_path, $resource_names)
    {
        if (!$this->isRunningHRM($public_path)) {
            return $this->gets($resource_names);
        }

        $hot = file_get_contents("{$public_path}/hot");
        $hot = rtrim($hot);

        return (new Collection($resource_names))
            ->assocBy(fn ($item) => [$item => $hot . $item])
            ->toArray()
        ;
    }

    /**
     * Determine if the HMR server is running.
     */
    public function isRunningHRM(string $public_path): bool
    {
        return is_file("{$public_path}/hot");
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
