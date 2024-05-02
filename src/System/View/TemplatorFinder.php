<?php

declare(strict_types=1);

namespace System\View;

use System\View\Exceptions\ViewFileNotFound;

class TemplatorFinder
{
    /**
     * View file location has register.
     *
     * @var string[]
     */
    protected array $views = [];

    /**
     * Paths.
     *
     * @var string[]
     */
    protected array $paths;

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
        $this->paths      = $paths;
        $this->extensions = $extensions ?? ['.temlate.php'];
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

        return $this->views[] = $this->findInPath($view_name, $this->paths);
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
                if (file_exists($path . $view_name . $extenstion)) {
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
                if (file_exists($find = $path . $view_name . $extenstion)) {
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
        if (!isset($this->paths[$path])) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Add exteention in first array.
     */
    public function addExctension(string $extension): self
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
}
