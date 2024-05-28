<?php

declare(strict_types=1);

namespace System\Integrate\Contracts;

trait Paths
{
    /**
     * Get base path/dir.
     *
     * @deprecated version 0.33.
     */
    public function base_path(): string
    {
        return $this->get('path.base');
    }

    /**
     * Get app path.
     *
     * @deprecated version 0.33.
     */
    public function app_path(): string
    {
        return $this->get('path.app');
    }

    /**
     * Get model path.
     *
     * @deprecated version 0.33.
     */
    public function model_path(): string
    {
        return $this->get('path.model');
    }

    /**
     * Get base view path.
     *
     * @deprecated version 0.33.
     */
    public function view_path(): string
    {
        return $this->get('path.view');
    }

    /**
     * Get view paths.
     *
     * @return string[]
     *
     * @deprecated version 0.33.
     */
    public function view_paths(): array
    {
        return $this->get('paths.view');
    }

    /**
     * Get controller path.
     *
     * @deprecated version 0.33.
     */
    public function controller_path(): string
    {
        return $this->get('path.controller');
    }

    /**
     * Get Services path.
     *
     * @deprecated version 0.33.
     */
    public function services_path(): string
    {
        return $this->get('path.services');
    }

    /**
     * Get component path.
     *
     * @deprecated version 0.33.
     */
    public function component_path(): string
    {
        return $this->get('path.component');
    }

    /**
     * Get command path.
     *
     * @deprecated version 0.33.
     */
    public function command_path(): string
    {
        return $this->get('path.command');
    }

    /**
     * Get storage path.
     *
     * @deprecated version 0.33.
     */
    public function storage_path(): string
    {
        return $this->get('path.storage');
    }

    /**
     * Get cache path.
     *
     * @deprecated version 0.33.
     * @deprecated version 0.33.
     */
    public function cache_path(): string
    {
        return $this->get('path.cache');
    }

    /**
     * Get compailed path.
     *
     * @deprecated version 0.33.
     */
    public function compiled_view_path(): string
    {
        return $this->get('path.compiled_view_path');
    }

    /**
     * Get config path.
     *
     * @deprecated version 0.33.
     */
    public function config_path(): string
    {
        return $this->get('path.config');
    }

    /**
     * Get middleware path.
     *
     * @deprecated version 0.33.
     */
    public function middleware_path(): string
    {
        return $this->get('path.middleware');
    }

    /**
     * Get provider path.
     *
     * @deprecated version 0.33.
     */
    public function provider_path(): string
    {
        return $this->get('path.provider');
    }

    /**
     * Get migration path.
     *
     * @deprecated version 0.33.
     */
    public function migration_path(): string
    {
        return $this->get('path.migration');
    }

    /**
     * Get seeder path.
     *
     * @deprecated version 0.33.
     */
    public function seeder_path(): string
    {
        return $this->get('path.seeder');
    }

    /**
     * Get public path.
     *
     * @deprecated version 0.33.
     */
    public function public_path(): string
    {
        return $this->get('path.public');
    }
}
