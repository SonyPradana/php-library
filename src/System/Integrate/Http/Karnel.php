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
        $this->app->set(Request::class, $request);

        $dispatcher = $this->dispatcher($request);

        $pipeline = array_reduce(
            array_merge($this->middleware, $dispatcher['middleware']),
            fn ($next, $middleware) => fn ($req) => $this->app->call([$middleware, 'handle'], ['request' => $req, 'next' => $next]),
            fn () => $this->responesType($dispatcher['callable'], $dispatcher['parameters'])
        );

        return $pipeline($request);
    }

    /**
     * @param callable|mixed[]|string $callable   function to call
     * @param mixed[]                 $parameters parameters to use
     *
     * @throws \Exception
     */
    private function responesType($callable, $parameters): Response
    {
        $content = $this->app->call($callable, $parameters);
        if ($content instanceof Response) {
            return $content;
        }

        if (is_string($content)) {
            return new Response($content);
        }

        if (is_array($content)) {
            return new Response($content);
        }

        throw new \Exception('Content must return as respone|string|array');
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatcher(Request $request): array
    {
        return ['callable' => new Response(), 'parameters' => [], 'middleware'];
    }
}
