<?php

declare(strict_types=1);

namespace System\Integrate;

class Vite
{
    private string $path;
    private string $manifest_name;

    public function __construct(string $path, string $manifest_name)
    {
        $this->path          = $path;
        $this->manifest_name = $manifest_name;
    }

    public function loader(): array
    {
        $file_name = $this->path . $this->manifest_name;
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
}
