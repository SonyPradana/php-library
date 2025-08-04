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
     */
    public function __invoke(string ...$entrypoints): string
    {
        $tags = [];

        if ($this->isRunningHRM()) {
            $tags[] = $this->getHmrScript();
            $assets = $this->gets($entrypoints);

            foreach ($assets as $entrypoint => $url) {
                $tags[] = $this->createTag($url, $entrypoint);
            }

            return implode("\n", $tags);
        }

        $assets = $this->gets($entrypoints);

        foreach ($assets as $entrypoint => $url) {
            if (!$this->isCssFile($entrypoint)) {
                $tags[] = $this->createPreloadTag($url);
            }
        }

        foreach ($assets as $entrypoint => $url) {
            if ($this->isCssFile($entrypoint)) {
                $tags[] = $this->createStyleTag($url);
            }
        }

        foreach ($assets as $entrypoint => $url) {
            if (!$this->isCssFile($entrypoint)) {
                $tags[] = $this->createScriptTag($url);
            }
        }

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

    /**
     * @param string[] $entrypoints
     */
    public function tagsPreload(array $entrypoints): string
    {
        if ($this->isRunningHRM()) {
            return '';
        }

        $tags   = [];
        $assets = $this->gets($entrypoints);

        foreach ($assets as $entrypoint => $url) {
            if (!$this->isCssFile($entrypoint)) {
                $tags[] = $this->createPreloadTag($url);
            }
        }

        return implode("\n", $tags);
    }

    /**
     * Generate tags with custom attributes.
     *
     * @param array<string, string|bool|int|null> $attributes
     * @param string[]                            $entrypoints
     */
    public function tagsWithAttributes(array $attributes, array $entrypoints): string
    {
        $tags = [];

        if ($this->isRunningHRM()) {
            $tags[] = $this->getHmrScript();
        }

        $assets = $this->gets($entrypoints);

        foreach ($assets as $entrypoint => $url) {
            $tags[] = $this->createTagWithAttributes($url, $entrypoint, $attributes);
        }

        return implode("\n", $tags);
    }

    /**
     * @param string[] $entrypoints
     */
    public function tags(array $entrypoints): string
    {
        $tags = [];

        if ($this->isRunningHRM()) {
            $tags[] = $this->getHmrScript();
        }

        $assets = $this->gets($entrypoints);

        foreach ($assets as $entrypoint => $url) {
            $tags[] = $this->createTag($url, $entrypoint);
        }

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
     * Create tag with custom attributes.
     *
     * @param array<string, string|bool|int|null> $attributes
     */
    private function createTagWithAttributes(string $url, string $entrypoint, array $attributes): string
    {
        $url   = $this->escapeUrl($url);
        $attrs = $this->buildAttributeString($attributes);

        if ($this->isCssFile($entrypoint) && !$this->isRunningHRM()) {
            return "<link rel=\"stylesheet\" href=\"{$url}\" {$attrs}>";
        }

        return "<script type=\"module\" src=\"{$url}\" {$attrs}></script>";
    }

    private function createTag(string $url, string $entrypoint): string
    {
        if ($this->isCssFile($entrypoint)) {
            return $this->createStyleTag($url);
        }

        return $this->createScriptTag($url);
    }

    private function createScriptTag(string $url): string
    {
        $url = $this->escapeUrl($url);

        return "<script type=\"module\" src=\"{$url}\"></script>";
    }

    private function createStyleTag(string $url): string
    {
        $url = $this->escapeUrl($url);

        if ($this->isRunningHRM()) {
            return "<script type=\"module\" src=\"{$url}\"></script>";
        }

        return "<link rel=\"stylesheet\" href=\"{$url}\">";
    }

    private function createPreloadTag(string $url): string
    {
        $url = $this->escapeUrl($url);

        return '<link rel="modulepreload" href="' . $url . '">';
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
