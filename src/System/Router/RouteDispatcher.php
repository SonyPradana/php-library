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
        $basepath   = rtrim($basepath, '/');
        $parsed_url = parse_url($this->request->getUrl());
        $path       = '/';

        if (isset($parsed_url['path'])) {
            if ($trailing_slash_matters) {
                $path = $parsed_url['path'];
            } else {
                $path = ($basepath . '/' != $parsed_url['path']) ? rtrim($parsed_url['path'], '/') : $parsed_url['path'];
            }
        }

        $method            = $this->request->getMethod();
        $path_match_found  = false;
        $route_match_found = false;

        foreach ($this->routes as $route) {
            $expression          = $route['expression'];
            $original_expression = $expression;

            // Build the regex expression
            $expression = $this->makeRouteExpression($expression, $basepath);

            if (preg_match("#{$expression}#" . ($case_matters ? '' : 'i') . 'u', $path, $matches)) {
                $path_match_found = true;

                foreach ((array) $route['method'] as $allowedMethod) {
                    if (strtolower($method) === strtolower($allowedMethod)) {
                        $namedMatches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        if (false === empty($namedMatches)) {
                            $cleanMatches = array_filter($namedMatches, static fn (string $key): bool => false === is_numeric($key), ARRAY_FILTER_USE_KEY);
                            $cleanMatches = array_values($cleanMatches);
                        } else {
                            array_shift($matches);
                            $cleanMatches = array_values($matches);
                        }

                        $this->trigger($this->found, [$route['function'], $cleanMatches], $route['middleware'] ?? []);
                        $this->current               = $route;
                        $this->current['expression'] = "^{$original_expression}$";
                        $route_match_found           = true;
                        break;
                    }
                }
            }

            if ($route_match_found && false === $multimatch) {
                break;
            }
        }

        if (false === $route_match_found) {
            if ($path_match_found && $this->method_not_allowed) {
                $this->trigger($this->method_not_allowed, [$path, $method]);
            } elseif (false === $path_match_found && $this->not_found) {
                $this->trigger($this->not_found, [$path]);
            }
        }
    }

    /**
     * Build the route expression regex from the route definition.
     */
    private function makeRouteExpression(string $expression, string $basepath): string
    {
        // Legacy support: (:slug), etc.
        foreach (Router::$patterns as $key => $pattern) {
            $expression = str_replace($key, $pattern, $expression);
        }

        // Named with type: (name:type)
        $expression = preg_replace_callback('/\((\w+):(\w+)\)/', static function ($m) {
            $pattern = Router::$patterns["(:{$m[2]})"] ?? '[^/]+';

            return "(?P<{$m[1]}>{$pattern})";
        }, $expression);

        if ($basepath !== '' && $basepath !== '/') {
            $expression = "({$basepath}){$expression}";
        }

        return "^{$expression}$";
    }
}
