<?php

namespace System\Router;

use System\Http\Response;

abstract class Controller
{
    public function __invoke(...$args): Response
    {
        return $this->callSelf($args['method'], $args['parameter']);
    }

    abstract public static function renderView(string $view_path, array $portal = [], int $status_code = Response::HTTP_OK, array $headers = []): Response;

    public function callSelf(string $method, array $parameter): Response
    {
        return call_user_func([$this, $method], array_values($parameter));
    }
}
