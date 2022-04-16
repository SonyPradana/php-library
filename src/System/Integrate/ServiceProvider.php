<?php

declare(strict_types=1);

namespace System\Integrate;

abstract class ServiceProvider
{
    /** @var array Class regiter */
    protected $register = [
        // register
    ];

    /**
     * Boot provider.
     */
    public function boot()
    {
        // boot
    }
}
