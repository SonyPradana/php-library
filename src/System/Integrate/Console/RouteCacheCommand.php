<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Integrate\Application;
use System\Router\Router;

use function System\Console\fail;
use function System\Console\ok;
use function System\Console\warn;

class RouteCacheCommand extends Command
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'route:cache',
            'fn'      => [self::class, 'cache'],
        ], [
            'pattern' => 'route:clear',
            'fn'      => [self::class, 'clear'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'config:cache' => 'Build route cache',
                'config:clear' => 'Remove route cache',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function cache(Application $app, Router $router): int
    {
        $routes = [];
        foreach ($router->getRoutesRaw() as $route) {
            if (is_callable($route['function'])) {
                warn("Route '{$route['name']}' cannot be cached because it contains a closure/callback function")->out();

                return 1;
            }

            $routes[] = [
                'method'     => $route['method'],
                'uri'        => $route['uri'],
                'expression' => $route['expression'],
                'function'   => $route['function'],
                'middleware' => $route['middleware'],
                'name'       => $route['name'],
            ];
        }
        $cached_route = '<?php return ' . var_export($routes, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'route.php', $cached_route)) {
            ok('Route file has successfully created.')->out();

            return 0;
        }
        fail('Cant build route cache.')->out();

        return 1;
    }

    public function clear(Application $app): int
    {
        if (file_exists($file = $app->getApplicationCachePath() . 'route.php')) {
            @unlink($file);
            ok('Clear route cache has successfully.')->out();

            return 0;
        }

        return 1;
    }
}
