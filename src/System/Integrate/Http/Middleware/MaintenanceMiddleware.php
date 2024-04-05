<?php

declare(strict_types=1);

namespace System\Integrate\Http\Middleware;

use System\Http\Request;
use System\Http\Response;

class MaintenanceMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        return $next($request);
    }
}
