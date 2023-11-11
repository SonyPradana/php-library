<?php

declare(strict_types=1);

namespace System\View;

class Manifestor
{
    private string $templateDir;
    private string $cache_path;
    private string $manifest_name;
    /** @var array<string, string[]> */
    private static $cache_manifest = [];

    public function __construct(string $templateDir, string $cache_path, string $manifest_name = '/manifest.json')
    {
        $this->templateDir   = $templateDir;
        $this->cache_path    = $cache_path;
        $this->manifest_name = $manifest_name;
    }

    public function manifestFileName(): string
    {
        return $this->cache_path . $this->manifest_name;
    }

    /**
     * Get manifest data as array paired.
     *
     * @return array<string, string[]>
     */
    public function getManifest()
    {
        if (false === file_exists($file_name = $this->manifestFileName())) {
            throw new \InvalidArgumentException("Manifest file name doest exist `{$file_name}`.");
        }

        $file = file_get_contents($file_name);

        if (false === $file) {
            throw new \Exception('Cant load manifet file.');
        }

        return self::$cache_manifest = (array) json_decode($file, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Get cached manifest data.
     *
     * @return array<string, string[]>
     */
    public static function getCachedManifest(string $template_path, string $cache_path, string $manifest_name = 'manifest.json')
    {
        if ([] === self::$cache_manifest) {
            (new self($template_path, $cache_path, $manifest_name))->getManifest();
        }

        return self::$cache_manifest;
    }

    public static function flushCachedManifest(): void
    {
        self::$cache_manifest = [];
    }

    /**
     * Put manifest as json data.
     *
     * @param array<string, string[]> $manifest
     */
    public function putManifest($manifest): bool
    {
        if (false === ($raw = json_encode($manifest, JSON_PRETTY_PRINT))) {
            return false;
        }

        if (false === file_put_contents($this->manifestFileName(), $raw)) {
            return false;
        }

        self::$cache_manifest = $manifest;

        return true;
    }

    public function hasManifest(): bool
    {
        return file_exists($this->manifestFileName());
    }

    public function init(): void
    {
        $this->putManifest([]);
    }

    /**
     * Get teplate dependency slot/include.
     *
     * @return string[]
     */
    public function getDependency(string $template_filename)
    {
        return $this->getManifest()[$template_filename] ?? [];
    }

    /**
     * Remove template file from manifest file.
     */
    public function removeDependency(string $template_filename): void
    {
        $new_manifest = [];
        foreach ($this->getManifest() as $template => $dependency) {
            if ($template === $template_filename) {
                continue;
            }

            $new_manifest[$template] = $dependency;
        }
        $this->putManifest($new_manifest);
    }

    /**
     * Replace dependecy to new (overwrite).
     *
     * @param string[] $new_dependency
     */
    public function replaceDependency(string $template_filename, $new_dependency): void
    {
        $dependency                     = $this->getManifest();
        $dependency[$template_filename] = $new_dependency;
        $this->putManifest(self::$cache_manifest = $dependency);
    }

    /**
     * Check template file depency is uptode to newst template.
     *
     * @throws \Exception when dependency file not exist
     */
    public function isDependencyUptodate(string $template_filename, int $template_time = null): bool
    {
        if (false === \file_exists($this->cache_path . '/' . $template_filename)) {
            throw new \Exception("Cache file `{$template_filename}` is not exist.");
        }

        if (null === $template_time) {
            $template_time = \filemtime($this->cache_path . '/' . $template_filename);
        }

        foreach ($this->getDependency($template_filename) as $file) {
            if (false === file_exists($check = $this->templateDir . '/' . $file)) {
                return false;
            }

            if (\filemtime($check) >= $template_time) {
                return false;
            }
        }

        return true;
    }
}
