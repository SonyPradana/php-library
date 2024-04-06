<?php

declare(strict_types=1);

namespace System\Integrate\Http\Middleware;

use System\Http\Request;
use System\Http\Response;
use System\Integrate\Application;

class MaintenanceMiddleware
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle(Request $request, \Closure $next): Response
    {
        if ($this->app->isDownMaintenanceMode()) {
            $data = $this->app->getDownData();

            if (isset($data['redirect'])) {
                return redirect($data['redirect']);
            }

            if (isset($data['template'])) {
                $header = isset($data['retry']) ? ['Retry-After' => $data['retry']] : [];

                return new Response($data['template'], $data['status'] ?? 503, $header);
            }
        }

        return $next($request);
    }
}
