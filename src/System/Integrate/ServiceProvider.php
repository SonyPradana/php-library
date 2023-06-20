<?php

declare(strict_types=1);

namespace System\Integrate;

abstract class ServiceProvider
{
    /** @var Application */
    protected $app;

    /** @var array<int|string, class-string> Class register */
    protected $register = [
        // register
    ];

    /**
     * Create a new service provider instance.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Boot provider.
     *
     * @return void
     */
    public function boot()
    {
        // boot
    }

    /**
     * Register to application container before booted.
     *
     * @return void
     */
    public function register()
    {
        // register application container
    }
}
