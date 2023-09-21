<?php

declare(strict_types=1);

namespace System\Router;

use System\Http\Request;

final class RouteDispatcher
{
    // dispatch
    /** @var Request */
    private $request;
    /** @var Route[] */
    private $routes = [];

    // callback ------------------
    /** @var callable */
    private $found;
    /** @var ?callable(string): mixed */
    private $not_found;
    /** @var ?callable(string, string): mixed */
    private $method_not_allowed;

    // setup --------------------
    private string $basepath             = '';
    private bool $case_matters           = false;
    private bool $trailing_slash_matters = false;
    private bool $multimatch             = false;

    /** @var array<string, mixed> */
    private $trigger;
    /** @var Route */
    private $current;

    /**
     * @param Request $request Incoming request
     * @param Route[] $routes  Array of route
     */
    public function __construct(Request $request, $routes)
    {
        $this->request = $request;
        $this->routes  = $routes;
    }

    /**
     * Create new costruct using uri and method.
     *
     * @param string  $uri    Ulr
     * @param string  $method Method
     * @param Route[] $routes Array of route
     */
    public static function dispatchFrom(string $uri, string $method, $routes): self
    {
        $create_request = new Request($uri, [], [], [], [], [], [], $method);

        return new static($create_request, $routes);
    }

    // setter -----------------------------------

    /**
     * Setup Base Path.
     *
     * @param string $base_path Base Path
     *
     * @return self
     */
    public function basePath(string $base_path)
    {
        $this->basepath = $base_path;

        return $this;
    }

    /**
     * Cese sensitive metters.
     *
     * @param bool $case_matters Cese sensitive metters
     *
     * @return self
     */
    public function caseMatters(bool $case_matters)
    {
        $this->case_matters = $case_matters;

        return $this;
    }

    /**
     * Trailing slash matters.
     *
     * @param bool $trailling_slash_metters Trailing slash matters
     *
     * @return self
     */
    public function trailingSlashMatters(bool $trailling_slash_metters)
    {
        $this->trailing_slash_matters = $trailling_slash_metters;

        return $this;
    }

    /**
     * Return Multy route.
     *
     * @param bool $multimath Return Multy route
     *
     * @return self
     */
    public function multimatch(bool $multimath)
    {
        $this->multimatch = $multimath;

        return $this;
    }

    // getter -----------------------------------

    /**
     * Get current router after dispatch.
     *
     * @return Route
     */
    public function current()
    {
        return $this->current;
    }

    // method -----------------------------------

    /**
     * Setup action and dispatch route.
     *
     * @return array<string, mixed> trigger arction ['callable' => callable, 'param' => param]
     */
    public function run(callable $found, callable $not_found, callable $method_not_allowed)
    {
        $this->found              = $found;
        $this->not_found          = $not_found;
        $this->method_not_allowed = $method_not_allowed;

        $this->dispatch($this->basepath, $this->case_matters, $this->trailing_slash_matters, $this->multimatch);

        return $this->trigger;
    }

    /**
     * Catch action from callable (found, not_found, method_not_allowed).
     *
     * @param callable                   $callable   Callback
     * @param array<int, mixed|string[]> $params     Callaback params
     * @param class-string[]             $middleware Array of middleware class-name
     */
    private function trigger(callable $callable, $params, $middleware = []): void
    {
        $this->trigger = [
            'callable'      => $callable,
            'params'        => $params,
            'middleware'    => $middleware,
        ];
    }

    /**
     * Dispatch routes and setup trigger.
     *
     * @param string $basepath               Base Path
     * @param bool   $case_matters           Cese sensitive metters
     * @param bool   $trailing_slash_matters Trailing slash matters
     * @param bool   $multimatch             Return Multy route
     */
    private function dispatch($basepath = '', $case_matters = false, $trailing_slash_matters = false, $multimatch = false): void
    {
        // The basepath never needs a trailing slash
        // Because the trailing slash will be added using the route expressions
        $basepath = rtrim($basepath, '/');

        // Parse current URL
        $parsed_url = parse_url($this->request->getUrl());

        $path = '/';

        // If there is a path available
        if (isset($parsed_url['path'])) {
            // If the trailing slash matters
            if ($trailing_slash_matters) {
                $path = $parsed_url['path'];
            } else {
                // If the path is not equal to the base path (including a trailing slash)
                if ($basepath . '/' != $parsed_url['path']) {
                    // Cut the trailing slash away because it does not matters
                    $path = rtrim($parsed_url['path'], '/');
                } else {
                    $path = $parsed_url['path'];
                }
            }
        }

        // Get current request method
        $method = $this->request->getMethod();

        $path_match_found  = false;
        $route_match_found = false;

        foreach ($this->routes as $route) {
            // If the method matches check the path

            // Add basepath to matching string
            if ($basepath != '' && $basepath != '/') {
                $route['expression'] = '(' . $basepath . ')' . $route['expression'];
            }

            // Add 'find string start' automatically
            $route['expression'] = '^' . $route['expression'];

            // Add 'find string end' automatically
            $route['expression'] .= '$';

            // Check path match
            if (preg_match('#' . $route['expression'] . '#' . ($case_matters ? '' : 'i') . 'u', $path, $matches)) {
                $path_match_found = true;

                // Cast allowed method to array if it's not one already, then run through all methods
                foreach ((array) $route['method'] as $allowedMethod) {
                    // Check method match
                    if (strtolower($method) == strtolower($allowedMethod)) {
                        array_shift($matches); // Always remove first element. This contains the whole string

                        if ($basepath != '' && $basepath != '/') {
                            array_shift($matches); // Remove basepath
                        }

                        // execute request
                        $this->trigger($this->found, [$route['function'], $matches], $route['middleware'] ?? []);
                        $this->current = $route;

                        $route_match_found = true;

                        // Do not check other routes
                        break;
                    }
                }
            }

            // Break the loop if the first found route is a match
            if ($route_match_found && !$multimatch) {
                break;
            }
        }

        // No matching route was found
        if (!$route_match_found) {
            // But a matching path exists
            if ($path_match_found) {
                if ($this->method_not_allowed) {
                    $this->trigger($this->method_not_allowed, [$path, $method]);
                }
            } else {
                if ($this->not_found) {
                    $this->trigger($this->not_found, [$path]);
                }
            }
        }
    }
}
