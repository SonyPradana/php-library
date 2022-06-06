<?php

namespace System\Router;

abstract class AbstractMiddleware
{
    /**
     * Run middleware.
     */
    public function handle()
    {
        // ovveridedable
    }

    /**
     * Use for clear middleware if needed.
     */
    public function close()
    {
        // ovveridedable
    }
}
