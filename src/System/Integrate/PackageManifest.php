<?php

declare(strict_types=1);

namespace System\Integrate;

final class PackageManifest
{
    /**
     * Cached package manifest.
     *
     * @var array<string, array<string, array<int, string>>>|null
     */
    public ?array $package_manifest = null;

    public function __construct(
        private string $base_path,
        private string $application_cache_path,
        private ?string $vendor_path = null
    ) {
        $this->vendor_path ??= DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get provider in cache package manifest.
     *
     * @return string[]
     */
    public function providers(): array
    {
        return $this->config()['providers'];
    }

    /**
     * Get array of provider..
     *
     * @return array<string, string[]>
     */
    protected function config(): array
    {
        $this->getPackageManifest();
        $entry = [
            'providers' => [],
        ];
        foreach ($this->package_manifest ?? [] as $package) {
            foreach ($package['providers'] ?? [] as $provider) {
                $entry['providers'][] = $provider;
            }
        }

        return $entry;
    }

    /**
     * Get cached package manifest has been build.
     *
     * @return array<string, array<string, array<int, string>>>
     */
    protected function getPackageManifest(): array
    {
        if ($this->package_manifest) {
            return $this->package_manifest;
        }

        if (false === $this->hasPackageManifest()) {
            $this->build();
        }

        return $this->package_manifest = require $this->application_cache_path . 'provider.php';
    }

    /**
     * Check package has package manifest (builed).
     */
    public function hasPackageManifest(): bool
    {
        return file_exists($this->application_cache_path . 'provider.php');
    }

    /**
     * Build cache package manifest from composer installed package.
     */
    public function build(): void
    {
        $packages = [];
        $provider = [];

        // vendor\composer\installed.json
        if (file_exists($file = $this->base_path . $this->vendor_path . 'installed.json')) {
            $installed = file_get_contents($file);
            $installed = json_decode($installed, true);

            $packages = $installed['packages'] ?? [];
        }

        foreach ($packages as $package) {
            $provider[$package['name']] = $package['extra']['savanna'] ?? [''];
        }
        array_filter($provider);

        file_put_contents($this->application_cache_path . 'provider.php', '<?php return ' . var_export($provider, true) . ';' . PHP_EOL);
    }
}
