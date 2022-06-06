<?php

declare(strict_types=1);

namespace System\Integrate;

use System\Container\Container;

abstract class ServiceProvider
{
    /** @var Application */
    protected $app;

    /** @var array Class register */
    protected $register = [
        // register
    ];

    /**
     * Create a new service provider instance.
     *
     * @param Application|System\Container\Container $app
     *
     * @return void
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Boot provider.
     */
    public function boot()
    {
        // boot
    }

    public function register()
    {
        // register application container
    }
}
