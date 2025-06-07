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
        $basepath          = rtrim($basepath, '/');
        $parsed_url        = parse_url($this->request->getUrl());
        $path              = $this->resolvePath($parsed_url, $basepath, $trailing_slash_matters);
        $method            = $this->request->getMethod();
        $path_match_found  = false;
        $route_match_found = false;

        foreach ($this->routes as $route) {
            $expression          = $route['expression'];
            $original_expression = $expression;
            $expression          = $this->makeRoutePatterns($expression, $route['patterns'] ?? []);

            if ($basepath !== '' && $basepath !== '/') {
                $expression = "({$basepath}){$expression}";
            }

            if (preg_match("#^{$expression}$#" . ($case_matters ? '' : 'i') . 'u', $path, $matches)) {
                $path_match_found = true;

                foreach ((array) $route['method'] as $allowedMethod) {
                    if (strtolower($method) !== strtolower($allowedMethod)) {
                        continue;
                    }

                    $parameters = $this->resolveNamedParameters($matches);

                    $this->trigger(
                        callable: $this->found,
                        params: [$route['function'], $parameters],
                        middleware: $route['middleware'] ?? []
                    );
                    $this->current               = $route;
                    $this->current['expression'] = "^{$original_expression}$";
                    $route_match_found           = true;
                    break;
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
     * Resolve path to santize trailing slash.
     *
     * @param array<string, int|string>|false $parsed_url
     */
    private function resolvePath($parsed_url, string $basepath, bool $trailing_slash_matters): string
    {
        $parsed_path = $parsed_url['path'] ?? null;

        return match (true) {
            null === $parsed_path           => '/',
            $trailing_slash_matters         => $parsed_path,
            "{$basepath}/" !== $parsed_path => rtrim($parsed_path, '/'),
            default                         => $parsed_path,
        };
    }

    /**
     * Parse expression with costume pattern.
     *
     * @param array<string, string> $pattern
     */
    private function makeRoutePatterns(string $expression, array $pattern): string
    {
        if ([] === $pattern) {
            return $expression;
        }

        return Router::mapPatterns($expression, $pattern);
    }

    /**
     * Resolve matches from preg_match path.
     *
     * @param string[] $matches
     *
     * @return array<string|int, string>
     */
    private function resolveNamedParameters(array $matches): array
    {
        $namedMatches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        if (empty($namedMatches)) {
            array_shift($matches);
            $cleanMatches = $matches;
        } else {
            $cleanMatches = array_filter($namedMatches, static fn (string $key): bool => false === is_numeric($key), ARRAY_FILTER_USE_KEY);
        }

        return $cleanMatches;
    }
}
