<?php

declare(strict_types=1);

namespace System\Router;

class Router
{
    /** @var Route[] */
    private static $routes           = [];
    private static $pathNotFound;
    private static $methodNotAllowed;
    public static $group             = [
        'prefix'     => '',
        'middleware' => [],
    ];
    /** @var Route */
    private static $current;

    /**
     * Alias router param to readable regex url.
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
     * @param array $route Router array format (expression, function, method)
     */
    public static function addRoutes(array $route)
    {
        if (isset($route['expression'])
    && isset($route['function'])
    && isset($route['method'])) {
            self::$routes[] = new Route($route);
        }
    }

    /**
     * Merge router array from other router array.
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
     * @return array Routes array
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

    public static function getRoutesRaw()
    {
        return self::$routes;
    }

    /**
     * Get current route.
     */
    public static function current(): Route
    {
        return self::$current;
    }

    /**
     * Reset all propery to be null.
     */
    public static function Reset()
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
     * Grouping routes using same prafix.
     *
     * @param string $prefix Prefix of router exprestion
     */
    public static function prefix(string $prefix)
    {
        return new RouteGroup(
            // set up
            function () use ($prefix) {
                Router::$group['prefix'] = $prefix;
            },
            // reset
            function () {
                Router::$group['prefix'] = '';
            }
        );
    }

    /**
     * Run mindle before run group route.
     *
     * @param array<int, class-string> $middlewares Middleware
     */
    public static function middleware(array $middlewares)
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

    public static function name(string $name)
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

    public static function controller(string $class_name)
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

    public static function group(array $setup_group, \Closure $group): void
    {
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

    public static function redirect(string $route_name): Route
    {
        foreach (self::$routes as $route) {
            if ($route_name === $route['name']) {
                return self::$current = $route;
            }
        }

        return self::$current;
    }

    /**
     * Function used to add a new route.
     *
     * @param array|string          $method   Methods allow
     * @param string                $uri      Route string or expression
     * @param callable|array|string $callback Function to call if route with allowed method is found
     */
    public static function match($method, string $uri, $callback)
    {
        $uri = self::$group['prefix'] . $uri;
        if (isset(self::$group['controller']) && is_string($callback)) {
            $callback = [self::$group['controller'], $callback];
        }
        $middleware = self::$group['middleware'] ?? [];

        return self::$routes[] = new Route([
      'method'      => $method,
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
    public static function any(string $expression, $function)
    {
        return self::match(['get', 'head', 'post', 'put', 'patch', 'delete', 'options'], $expression, $function);
    }

    /**
     * Function used to add a new route [method: get].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function get(string $expression, $function)
    {
        return self::match(['get', 'head'], $expression, $function);
    }

    /**
     * Function used to add a new route [method: post].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function post(string $expression, $function)
    {
        return self::match('post', $expression, $function);
    }

    /**
     * Function used to add a new route [method: put].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function put(string $expression, $function)
    {
        return self::match('put', $expression, $function);
    }

    /**
     * Function used to add a new route [method: patch].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function patch(string $expression, $function)
    {
        return self::match('patch', $expression, $function);
    }

    /**
     * Function used to add a new route [method: delete].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function delete(string $expression, $function)
    {
        return self::match('delete', $expression, $function);
    }

    /**
     * Function used to add a new route [method: options].
     *
     * @param string   $expression Route string or expression
     * @param callable $function   Function to call if route with allowed method is found
     */
    public static function options(string $expression, $function)
    {
        return self::match('options', $expression, $function);
    }

    /**
     * Result when route expression not register/found.
     *
     * @param callable $function Function to be Call
     */
    public static function pathNotFound($function)
    {
        self::$pathNotFound = $function;
    }

    /**
     * Result when route method not match/allowed.
     *
     * @param callable $function Function to be Call
     */
    public static function methodNotAllowed($function)
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
    public static function run($basepath = '', $case_matters = false, $trailing_slash_matters = false, $multimatch = false)
    {
        $dispatcher = RouteDispatcher::dispatchFrom($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], self::$routes);

        $dispatch = $dispatcher
        ->basePath($basepath)
        ->caseMatters($case_matters)
        ->trailingSlashMatters($trailing_slash_matters)
        ->multimatch($multimatch)
        ->run(
            fn ($current, $params) => call_user_func_array($current, $params),
            fn ($path) => call_user_func_array(self::$pathNotFound, [$path]),
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
