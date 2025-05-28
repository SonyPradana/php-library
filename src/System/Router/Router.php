<?php

declare(strict_types=1);

namespace System\Router;

class Router
{
    /** @var Route[] */
    private static $routes           = [];
    /** @var ?callable(string): mixed */
    private static $pathNotFound;
    /** @var ?callable(string, string): mixed */
    private static $methodNotAllowed;
    /** @var array<string, string|string[]> */
    public static $group             = [
        'prefix'     => '',
        'middleware' => [],
    ];
    /** @var Route|null */
    private static $current;

    /**
     * Alias router param to readable regex url.
     *
     * @var array<string, string>
     */
    public static $patterns = [
        '(:id)'   => '(\d+)',
        '(:num)'  => '([0-9]*)',
        '(:text)' => '([a-zA-Z]*)',
        '(:any)'  => '([0-9a-zA-Z_+-]*)',
        '(:slug)' => '([0-9a-zA-Z_-]*)',
        '(:all)'  => '(.*)',
    ];

    /**
     * Repalce alias to regex.
     *
     * @param string $url Alias patern url
     *
     * @return string Patern regex
     */
    public static function mapPatterns(string $url): string
    {
        $user_pattern         = array_keys(self::$patterns);
        $allow_pattern        = array_values(self::$patterns);

        return str_replace($user_pattern, $allow_pattern, $url);
    }

    /**
     * Adding new router using array of router.
     *
     * @param Route[] $route Router array format (expression, function, method)
     */
    public static function addRoutes(array $route): void
    {
        if (isset($route['expression'])
        && isset($route['function'])
        && isset($route['method'])) {
            self::$routes[] = new Route($route);
        }
    }

    /**
     * Remove router using router name.
     */
    public static function removeRoutes(string $route_name): void
    {
        foreach (self::$routes as $name => $route) {
            if ($route['name'] === $route_name) {
                unset(self::$routes[$name]);
            }
        }
    }

    /**
     * Change exists route using router name.
     *
     * @param Route $new_route
     */
    public static function changeRoutes(string $route_name, $new_route): void
    {
        foreach (self::$routes as $name => $route) {
            if ($route['name'] === $route_name) {
                self::$routes[$name] = $new_route;
                break;
            }
        }
    }

    /**
     * Merge router array from other router array.
     *
     * @param Route[][] $array_routes
     */
    public static function mergeRoutes(array $array_routes): void
    {
        foreach ($array_routes as $route) {
            self::addRoutes($route);
        }
    }

    /**
     * Get routes array.
     *
     * @return Route[] Routes array
     */
    public static function getRoutes()
    {
        $routes = [];
        foreach (self::$routes as $route) {
            // @phpstan-ignore-next-line
            $routes[] = $route->route();
        }

        return $routes;
    }

    /**
     * @return Route[]
     */
    public static function getRoutesRaw()
    {
        return self::$routes;
    }

    /**
     * Get current route.
     *
     * @return Route|null
     */
    public static function current()
    {
        return self::$current;
    }

    /**
     * Reset all propery to be null.
     */
    public static function Reset(): void
    {
        self::$routes           = [];
        self::$pathNotFound     = null;
        self::$methodNotAllowed = null;
        self::$group            = [
            'prefix'  => '',
            'as'      => '',
        ];
    }

    /**
     * Grouping routes using same prefix.
     *
     * @param string $prefix Prefix of router expression
     */
    public static function prefix(string $prefix): RouteGroup
    {
        $previous_prefix = self::$group['prefix'];

        return new RouteGroup(
            // set up
            function () use ($prefix, $previous_prefix) {
                Router::$group['prefix'] = $previous_prefix . $prefix;
            },
            // reset
            function () use ($previous_prefix) {
                Router::$group['prefix'] = $previous_prefix;
            }
        );
    }

    /**
     * Run mindle before run group route.
     *
     * @param array<int, class-string> $middlewares Middleware
     */
    public static function middleware(array $middlewares): RouteGroup
    {
        $reset_group = self::$group;

        return new RouteGroup(
            // load midleware
            function () use ($middlewares) {
                self::$group['middleware'] = $middlewares;
            },
            // close midleware
            function () use ($reset_group) {
                self::$group = $reset_group;
            }
        );
    }

    public static function name(string $name): RouteGroup
    {
        return new RouteGroup(
            // setup
            function () use ($name) {
                Router::$group['as'] = $name;
            },
            // reset
            function () {
                Router::$group['as'] = '';
            }
        );
    }

    public static function controller(string $class_name): RouteGroup
    {
        // backup current route
        $reset_group = self::$group;

        $route_group = new RouteGroup(
            // setup
            function () use ($class_name) {
                self::$group['controller'] = $class_name;
            },
            // reset
            function () use ($reset_group) {
                self::$group = $reset_group;
            }
        );

        return $route_group;
    }

    /**
     * @param array<string, string|string> $setup_group
     */
    public static function group(array $setup_group, \Closure $group): void
    {
        self::$group['middleware'] ??= [];

        // backup currect
        $reset_group = self::$group;

        $route_group = new RouteGroup(
            // setup
            function () use ($setup_group) {
                foreach ((array) self::$group['middleware'] as $middleware) {
                    $setup_group['middleware'][] = $middleware;
                }
                self::$group = $setup_group;
            },
            // reset
            function () use ($reset_group) {
                self::$group = $reset_group;
            }
        );

        $route_group->group($group);
    }

    public static function has(string $route_name): bool
    {
        foreach (self::$routes as $route) {
            if ($route_name === $route['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect to antother route.
     *
     * @throws \Exception
     */
    public static function redirect(string $to): Route
    {
        foreach (self::$routes as $name => $route) {
            if ($route['name'] === $to) {
                return self::$routes[$name];
            }
        }

        throw new \Exception('Route name doest exist.');
    }

    /**
     * @param class-string            $class_name
     * @param array<string, string[]> $setup
     */
    public static function resource(string $uri, $class_name, array $setup = []): ResourceControllerCollection
    {
        $setup['map'] ??= ResourceController::method();

        $resource = new ResourceController($uri, $class_name, $setup['map']);

        if (isset($setup['only'])) {
            $resource->only($setup['only']);
        }
        if (isset($setup['except'])) {
            $resource->except($setup['except']);
        }

        $resource->get()->each(function ($route) {
            self::$routes[] = $route;

            return true;
        });

        $router = new ResourceControllerCollection($class_name);

        if (array_key_exists('missing', $setup)) {
            $router->missing($setup['missing']);
        }

        return $router;
    }

    /**
     * Function used to add a new route.
     *
     * @param string|string[]          $method   Methods allow
     * @param string                   $uri      Route string or expression
     * @param callable|string|string[] $callback Function to call if route with allowed method is found
     */
    public static function match($method, string $uri, $callback): Route
    {
        $uri = self::$group['prefix'] . $uri;
        if (isset(self::$group['controller']) && is_string($callback)) {
            $callback = [self::$group['controller'], $callback];
        }
        $middleware = self::$group['middleware'] ?? [];

        return self::$routes[] = new Route([
            'method'      => $method,
            'uri'         => $uri,
            'expression'  => self::mapPatterns($uri),
            'function'    => $callback,
            'middleware'  => $middleware,
        ]);
    }

    /**
     * Function used to add a new route [any method].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function any(string $expression, $function): Route
    {
        return self::match(['get', 'head', 'post', 'put', 'patch', 'delete', 'options'], $expression, $function);
    }

    /**
     * Function used to add a new route [method: get].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function get(string $expression, $function): Route
    {
        return self::match(['get', 'head'], $expression, $function);
    }

    /**
     * Function used to add a new route [method: post].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function post(string $expression, $function): Route
    {
        return self::match('post', $expression, $function);
    }

    /**
     * Function used to add a new route [method: put].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function put(string $expression, $function): Route
    {
        return self::match('put', $expression, $function);
    }

    /**
     * Function used to add a new route [method: patch].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function patch(string $expression, $function): Route
    {
        return self::match('patch', $expression, $function);
    }

    /**
     * Function used to add a new route [method: delete].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function delete(string $expression, $function): Route
    {
        return self::match('delete', $expression, $function);
    }

    /**
     * Function used to add a new route [method: options].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function options(string $expression, $function): Route
    {
        return self::match('options', $expression, $function);
    }

    /**
     * Result when route expression not register/found.
     *
     * @param callable $function Function to be Call
     */
    public static function pathNotFound($function): void
    {
        self::$pathNotFound = $function;
    }

    /**
     * Result when route method not match/allowed.
     *
     * @param callable $function Function to be Call
     */
    public static function methodNotAllowed($function): void
    {
        self::$methodNotAllowed = $function;
    }

    /**
     * Run/execute routes.
     *
     * @param string $basepath               Base Path
     * @param bool   $case_matters           Cese sensitive metters
     * @param bool   $trailing_slash_matters Trailing slash matters
     * @param bool   $multimatch             Return Multy route
     */
    public static function run($basepath = '', $case_matters = false, $trailing_slash_matters = false, $multimatch = false): mixed
    {
        $dispatcher = RouteDispatcher::dispatchFrom($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], self::$routes);

        $dispatch = $dispatcher
            ->basePath($basepath)
            ->caseMatters($case_matters)
            ->trailingSlashMatters($trailing_slash_matters)
            ->multimatch($multimatch)
            ->run(
                fn ($current, $params) => call_user_func_array($current, $params),
                fn ($path)          => call_user_func_array(self::$pathNotFound, [$path]),
                fn ($path, $method) => call_user_func_array(self::$methodNotAllowed, [$path, $method])
            );

        self::$current = $dispatcher->current();

        // run middleware
        $middleware_used = [];
        foreach ($dispatch['middleware'] as $middleware) {
            if (in_array($middleware, $middleware_used)) {
                continue;
            }

            $middleware_used[]  = $middleware;
            $middleware_class   = new $middleware();
            $middleware_class->handle();
        }

        return call_user_func_array($dispatch['callable'], $dispatch['params']);
    }
}
