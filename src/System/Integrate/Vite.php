<?php

declare(strict_types=1);

namespace System\Integrate;

class Vite
{
    private string $build_path;
    private string $manifest_name;

    public function __construct(string $build_path, string $manifest_name)
    {
        $this->build_path          = $build_path;
        $this->manifest_name       = $manifest_name;
    }

    public function loader(): array
    {
        $file_name = "{$this->build_path}/{$this->manifest_name}";
        if (!file_exists($file_name)) {
            throw new \Exception("Manifest file not found {$file_name}");
        }

        $load = file_get_contents($file_name);
        $json = json_decode($load, true);

        if ($json === false) {
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
     * Determine if the HMR server is running.
     */
    public function isRunningHRM(string $public_path): bool
    {
        return is_file("{$public_path}/hot");
    }
}
