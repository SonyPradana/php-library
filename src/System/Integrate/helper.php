<?php

declare(strict_types=1);

// path aplication

use System\Http\RedirectResponse;
use System\Integrate\Exceptions\ApplicationNotAvailable;
use System\Router\Router;

if (!function_exists('app_path')) {
    /**
     * Get full aplication path, base on config file.
     *
     * @param string $folder_name Special path name
     *
     * @return string Application path folder
     */
    function app_path(string $folder_name): string
    {
        $path = app()->app_path();

        return $path . DIRECTORY_SEPARATOR . $folder_name;
    }
}

if (!function_exists('model_path')) {
    /**
     * Get aplication model path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Model path folder
     */
    function model_path(string $surfix_path = ''): string
    {
        $path = app()->model_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('view_path')) {
    /**
     * Get aplication view path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string View path folder
     */
    function view_path(string $surfix_path = ''): string
    {
        $path = app()->view_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('controllers_path')) {
    /**
     * Get aplication controllers path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Controller path folder
     */
    function controllers_path(string $surfix_path = ''): string
    {
        $path = app()->controller_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('services_path')) {
    /**
     * Get aplication services path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Service path folder
     */
    function services_path(string $surfix_path = ''): string
    {
        $path = app()->services_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('component_path')) {
    /**
     * Get aplication component path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Component path folder
     */
    function component_path(string $surfix_path = ''): string
    {
        $path = app()->component_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('commands_path')) {
    /**
     * Get aplication commands path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Command path folder
     */
    function commands_path(string $surfix_path = ''): string
    {
        $path = app()->command_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('cache_path')) {
    /**
     * Get aplication cache path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Cache path folder
     */
    function cache_path(string $surfix_path = ''): string
    {
        $path = app()->cache_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get aplication config path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Config path folder
     */
    function config_path(string $surfix_path = ''): string
    {
        $path = app()->config_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('middleware_path')) {
    /**
     * Get aplication middleware path, base on config file.
     *
     * @param string $surfix_path Add string end of path
     *
     * @return string Middleware path folder
     */
    function middleware_path(string $surfix_path = ''): string
    {
        $path = app()->middleware_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('provider_path')) {
    function provider_path(string $surfix_path = ''): string
    {
        $path = app()->provider_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('migration_path')) {
    function migration_path(string $surfix_path = ''): string
    {
        $path = app()->migration_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('seeder_path')) {
    function seeder_path(string $surfix_path = ''): string
    {
        $path = app()->seeder_path() . $surfix_path;

        return $path;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path.
     *
     * @param string $insert_path
     *                            Insert string in end of path
     *
     * @return string
     *                Base path folder
     */
    function base_path(string $insert_path = ''): string
    {
        return app()->base_path() . $insert_path;
    }
}

// app config

if (!function_exists('app_env')) {
    /**
     * Cek application environment mode.
     *
     * @return string Application environment mode
     */
    function app_env(): string
    {
        return app()->environment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Cek application production mode.
     *
     * @return bool True if in production mode
     */
    function is_production(): bool
    {
        return app()->isProduction();
    }
}

if (!function_exists('is_dev')) {
    /**
     * Cek application developent mode.
     *
     * @return bool True if in dev moded
     */
    function is_dev(): bool
    {
        return app()->isDev();
    }
}

if (!function_exists('app')) {
    /**
     * Get Application container.
     */
    function app(): System\Integrate\Application
    {
        $app = System\Integrate\Application::getIntance();
        if (null === $app) {
            throw new ApplicationNotAvailable();
        }

        return $app;
    }
}

if (!function_exists('config')) {
    /**
     * Get Application Configuration.
     *
     * @return System\Collection\CollectionImmutable<string, mixed>
     */
    function config()
    {
        return new System\Collection\CollectionImmutable(app()->get('config'));
    }
}

if (!function_exists('view')) {
    /**
     * Render with costume template engine, wrap in `Route\Controller`.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $option
     */
    function view(string $view_path, array $data = [], array $option = []): System\Http\Response
    {
        /** @var System\Http\Response */
        $view        = app()->get('view.response');
        $status_code = $option['status'] ?? 200;
        $headers     = $option['header'] ?? [];

        return $view($view_path, $data)
            ->setResponeCode($status_code)
            ->setHeaders($headers);
    }
}

if (!function_exists('vite')) {
    /**
     * Get resource using entri ponit(s).
     *
     * @param string $entry_ponits
     *
     * @return array<string, string>|string
     */
    function vite(...$entry_ponits)
    {
        /** @var System\Integrate\Vite */
        $vite = app()->get('vite.gets');

        return $vite(...$entry_ponits);
    }
}

if (!function_exists('redirect_route')) {
    /**
     * Redirect to another route.
     *
     * @param string[] $parameter Dinamic parameter to fill with url exprestion
     */
    function redirect_route(string $route_name, array $parameter = []): RedirectResponse
    {
        $route      = Router::redirect($route_name);
        $valueIndex = 0;
        $url        = preg_replace_callback(
            "/\(:\w+\)/",
            function ($matches) use ($parameter, &$valueIndex) {
                if (!array_key_exists($matches[0], Router::$patterns)) {
                    throw new \Exception('parameter not matches with any pattern.');
                }

                if ($valueIndex < count($parameter)) {
                    $value = $parameter[$valueIndex];
                    $valueIndex++;

                    return $value;
                }

                return '';
            },
            $route['uri']
        );

        return new RedirectResponse($url);
    }
}
