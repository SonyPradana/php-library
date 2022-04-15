<?php

// declare(strict_types=1);

namespace System\Integrate\Http;

use System\Container\Container;
use System\Http\Request;
use System\Http\Response;

class Karnel
{
    /** @var Container */
    protected $app;

    /**
     * Set instance.
     *
     * @param Container $app Application container
     * */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Handle http request.
     * 
     * @return Respone
     */
    public function handle(Request $request)
    {
        return new Response();
    }
}
