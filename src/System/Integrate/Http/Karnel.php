<?php

declare(strict_types=1);

namespace System\Integrate\Http;

use System\Container\Container;
use System\Http\Request;
use System\Http\Response;

class Karnel
{
    /** @var Container */
    protected $app;

    /** @var array<int, class-string> Global middleware */
    protected $middleware = [];

    /** @var array<int, class-string> Middleware has register */
    protected $middleware_used = [];

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
     * @param Request $request Incoming request
     *
     * @return Response Respone handle
     */
    public function handle(Request $request)
    {
        return new Response();
    }

    /**
     * Handle middleware class.
     *
     * @param array<int, class-string> $middlewares Middleware array class-name
     *
     * @return self
     */
    protected function handle_middleware($middlewares)
    {
        foreach ($middlewares as $middleware) {
            // prevent duplicate middleware
            if (in_array($middleware, $this->middleware_used)) {
                continue;
            }

            $this->app->call([$middleware, 'handle']);
            $this->middleware_used[] = $middleware;
        }

        return $this;
    }

    /**
     * Handle middleware and execute callback.
     *
     * @param callable                  $callable   Callable
     * @param array<int|string, string> $params     Parameter to use
     * @param array<int, class-string>  $middleware Middleware array class-name
     *
     * @return mixed Callavle result
     */
    protected function call_middleware($callable, $params = [], $middleware = [])
    {
        // global middleware
        $this->handle_middleware($this->middleware);

        // user middleware
        $this->handle_middleware($middleware);

        return $this->app->call($callable, $params);
    }
}
