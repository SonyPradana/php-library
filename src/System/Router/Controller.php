<?php

namespace System\Router;

use System\Http\Response;

abstract class Controller
{
    public function __invoke(string $invoke, array $parameter = []): Response
    {
        return call_user_func([$this, $invoke], $parameter);
    }

    abstract public static function renderView(string $view_path, array $portal = [], int $status_code = Response::HTTP_OK, array $headers = []): Response;
}
