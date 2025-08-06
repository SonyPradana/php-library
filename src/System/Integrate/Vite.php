<?php

declare(strict_types=1);

namespace System\Integrate;

class Vite
{
    private string $manifest_name;
    private int $cache_time = 0;
    /** @var array<string, array<string, array<string, string>>> */
    public static array $cache = [];
    public static ?string $hot = null;

    public function __construct(
        public string $public_path,
        public string $build_path,
    ) {
        $this->manifest_name        = 'manifest.json';
    }

    /**
     * Get resource using entri ponit(s).
     */
    public function __invoke(string ...$entrypoints): string
    {
        if (empty($entrypoints)) {
            return '';
        }

        if ($this->isRunningHRM()) {
            $tags   = [];
            $tags[] = $this->getHmrScript();
            $hmrUrl = $this->getHmrUrl();

            foreach ($entrypoints as $entrypoint) {
                $url    = $hmrUrl . $entrypoint;
                $tags[] = $this->createTag($url, $entrypoint);
            }

            return implode("\n", $tags);
        }

        $imports = $this->getManifestImports($entrypoints);
        $preload = [];
        foreach ($imports['imports'] as $entrypoint) {
            $url       = $this->getManifest($entrypoint);
            $preload[] = $this->createPreloadTag($url);
        }

        foreach ($imports['css'] as $entrypoint) {
            $preload[] = $this->createStyleTag($this->build_path . $entrypoint);
        }

        $assets    = $this->gets($entrypoints);
        $cssAssets = array_filter(
            $assets,
            fn ($file, $url) => $this->isCssFile($file),
            ARRAY_FILTER_USE_BOTH
        );

        $jsAssets = array_diff_key($assets, $cssAssets);
        $tags     = array_merge(
            $preload,
            array_map(fn ($url) => $this->createStyleTag($url), $cssAssets),
            array_map(fn ($url) => $this->createScriptTag($url), $jsAssets)
        );

        return implode("\n", $tags);
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
     * @return array<string, array<string, string|string[]>>
     */
    public function loader(): array
    {
        $file_name    = $this->manifest();
        $current_time = $this->manifestTime();

        if (array_key_exists($file_name, static::$cache)
        && $this->cache_time === $current_time) {
            return static::$cache[$file_name];
        }

        $this->cache_time = $current_time;
        $load             = file_get_contents($file_name);

        if ($load === false) {
            throw new \Exception("Failed to read manifest file: {$file_name}");
        }

        $json = json_decode($load, true);

        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Manifest JSON decode error: ' . json_last_error_msg());
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
     *
     * @deprecated Since v0.40
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
     * @param string[] $resources
     *
     * @return array{imports: string[], css: string[]}
     */
    public function getManifestImports(array $resources): array
    {
        $assets      = $this->loader();
        $resourceSet = array_fill_keys($resources, true);

        $preload = ['imports' => [], 'css' => []];

        foreach ($assets as $name => $asset) {
            if (isset($resourceSet[$name])) {
                $this->collectImports($assets, $asset, $preload);
            }
        }

        $preload['imports'] = array_values(array_unique($preload['imports']));
        $preload['css']     = array_values(array_unique($preload['css']));

        return $preload;
    }

    /**
     * @param array<string, array<string, string|string[]>> $assets
     * @param array<string, string|string[]>                $asset
     * @param array{imports: string[], css: string[]}       $preload
     */
    private function collectImports(array $assets, array $asset, array &$preload): void
    {
        if (false === empty($asset['css'])) {
            $preload['css'] = array_merge($preload['css'], $asset['css']);
        }

        if (false === empty($asset['imports'])) {
            foreach ($asset['imports'] as $import) {
                $preload['imports'][] = $import;

                if (isset($assets[$import])) {
                    $this->collectImports($assets, $assets[$import], $preload);
                }
            }
        }
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

        return array_combine(
            $resource_names,
            array_map(fn ($asset) => $hot . $asset, $resource_names)
        );
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

        $hotFile = "{$this->public_path}/hot";
        $hot     = file_get_contents($hotFile);

        if ($hot === false) {
            throw new \Exception("Failed to read hot file: {$hotFile}");
        }

        $hot  = rtrim($hot);
        $dash = str_ends_with($hot, '/') ? '' : '/';

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

    /**
     * @param string[] $entrypoints
     */
    public function tagsPreload(array $entrypoints): string
    {
        if ($this->isRunningHRM()) {
            return '';
        }

        $tags    = [];
        $imports = $this->getManifestImports($entrypoints);

        foreach ($imports['imports'] as $entrypoint) {
            $url    = $this->get($entrypoint);
            $tags[] = $this->createPreloadTag($url);
        }

        foreach ($imports['css'] as $entrypoint) {
            $tags[] = $this->createStyleTag($this->build_path . $entrypoint);
        }

        return implode("\n", $tags);
    }

    /**
     * @param string[]                            $entrypoints
     * @param array<string, string|bool|int|null> $attributes
     */
    public function tags(array $entrypoints, ?array $attributes = null): string
    {
        $tags = [];

        if ($this->isRunningHRM()) {
            $tags[] = $this->getHmrScript();
        }

        $assets    = $this->gets($entrypoints);
        $cssAssets = array_filter(
            $assets,
            fn ($file, $url) => $this->isCssFile($file),
            ARRAY_FILTER_USE_BOTH
        );

        $jsAssets = array_diff_key($assets, $cssAssets);
        $tags     = array_merge(
            array_map(fn ($url) => $this->createStyleTag($url, $attributes), $cssAssets),
            array_map(fn ($url) => $this->createScriptTag($url, $attributes), $jsAssets)
        );

        return implode("\n", $tags);
    }

    public function tag(string $entrypoint): string
    {
        if ($this->isRunningHRM()) {
            return $this->getHmrScript();
        }

        $url = $this->get($entrypoint);

        return $this->createTag($url, $entrypoint);
    }

    /**
     * @param array<string, string|bool|int|null> $attributes
     */
    private function createTag(string $url, string $entrypoint, ?array $attributes = null): string
    {
        if ($this->isCssFile($entrypoint)) {
            return $this->createStyleTag($url);
        }

        return $this->createScriptTag($url, $attributes);
    }

    /**
     * @param array<string, string|bool|int|null> $attributes
     */
    private function createScriptTag(string $url, ?array $attributes = null): string
    {
        if (false === isset($attributes['type'])) {
            $attributes = array_merge(['type' => 'module'], $attributes ?? []);
        }

        $attributes['src'] = $this->escapeUrl($url);
        $attributes        = $this->buildAttributeString($attributes);

        return "<script {$attributes}></script>";
    }

    /**
     * @param array<string, string|bool|int|null> $attributes
     */
    private function createStyleTag(string $url, ?array $attributes = null): string
    {
        if ($this->isRunningHRM()) {
            return $this->createScriptTag($url, $attributes);
        }

        $attributes ??= [];
        $attributes['rel']  = 'stylesheet';
        $attributes['href'] = $this->escapeUrl($url);
        $attributes         = $this->buildAttributeString($attributes);

        return "<link {$attributes} />";
    }

    private function createPreloadTag(string $url): string
    {
        $attributes['rel']  = 'modulepreload';
        $attributes['href'] = $this->escapeUrl($url);
        $attributes         = $this->buildAttributeString($attributes);

        return "<link {$attributes} />";
    }

    // helper functions

    private function isCssFile(string $filename): bool
    {
        return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $filename) === 1;
    }

    private function escapeUrl(string $url): string
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Build attribute string from array.
     *
     * @param array<string, string|bool|int|null> $attributes
     */
    private function buildAttributeString(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $parts = [];
        foreach ($attributes as $key => $value) {
            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');

            $part = match (true) {
                is_bool($value) => $value ? $key : null,
                $value === null => null,
                default         => $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"',
            };

            if ($part !== null) {
                $parts[] = $part;
            }
        }

        return implode(' ', $parts);
    }
}
