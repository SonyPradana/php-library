<?php

declare(strict_types=1);

namespace System\View;

use System\View\Exceptions\ViewFileNotFound;

class TemplatorFinder
{
    /**
     * View file location has register.
     *
     * @var array<string, string>
     */
    protected array $views = [];

    /**
     * Paths.
     *
     * @var string[]
     */
    protected array $paths = [];

    /**
     * Extetions.
     *
     * @var string[]
     */
    protected array $extensions;

    /**
     * Create new View Finder intance.
     *
     * @param string[] $paths
     * @param string[] $extensions
     */
    public function __construct(array $paths, ?array $extensions = null)
    {
        $this->setPaths($paths);
        $this->extensions = $extensions ?? ['.template.php', '.php'];
    }

    /**
     * Find file location by view_name given.
     *
     * @throws ViewFileNotFound
     */
    public function find(string $view_name): string
    {
        if (isset($this->views[$view_name])) {
            return $this->views[$view_name];
        }

        return $this->views[$view_name] = $this->findInPath($view_name, $this->paths);
    }

    /**
     * Check view name exist.
     */
    public function exists(string $view_name): bool
    {
        if (isset($this->views[$view_name])) {
            return true;
        }

        foreach ($this->paths as $path) {
            foreach ($this->extensions as $extenstion) {
                if (file_exists($file = $path . DIRECTORY_SEPARATOR . $view_name . $extenstion)) {
                    $this->views[$view_name] = $file;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find view name posible paths given.
     *
     * @param string[] $paths
     *
     * @throws ViewFileNotFound
     */
    protected function findInPath(string $view_name, array $paths): string
    {
        foreach ($paths as $path) {
            foreach ($this->extensions as $extenstion) {
                if (file_exists($find = $path . DIRECTORY_SEPARATOR . $view_name . $extenstion)) {
                    return $find;
                }
            }
        }

        throw new ViewFileNotFound($view_name);
    }

    /**
     * Add path to posible path location.
     */
    public function addPath(string $path): self
    {
        if (false === in_array($path, $this->paths)) {
            $this->paths[] = $this->resolvePath($path);
        }

        return $this;
    }

    /**
     * Add exteention in first array.
     */
    public function addExtension(string $extension): self
    {
        array_unshift($this->extensions, $extension);

        return $this;
    }

    /**
     * Flush view register file location.
     */
    public function flush(): void
    {
        $this->views = [];
    }

    /**
     * Set paths registered.
     *
     * @param string[] $paths
     */
    public function setPaths(array $paths): self
    {
        $this->paths = [];
        foreach ($paths as $path) {
            $this->paths[] = $this->resolvePath($path);
        }

        return $this;
    }

    /**
     * Get paths registered.
     *
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get Extension registered.
     *
     * @return string[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Resolve the path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function resolvePath($path)
    {
        return realpath($path) ?: $path;
    }
}
