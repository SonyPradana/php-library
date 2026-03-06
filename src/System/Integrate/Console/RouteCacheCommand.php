<?php

declare(strict_types=1);

namespace System\Integrate\Console;

use System\Console\Command;
use System\Integrate\Application;
use System\Router\Router;
use System\Template\VarExport;

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
                'route:cache' => 'Build route cache',
                'route:clear' => 'Remove route cache',
            ],
            'options'   => [
                '--files' => 'Cache spesific router files.',
            ],
            'relation'  => [
                'route:cache' => ['--files'],
            ],
        ];
    }

    public function cache(Application $app, Router $router): int
    {
        if (false !== ($files = $this->option('files', false))) {
            $router->Reset();
            $files = is_string($files) ? [$files] : $files;
            foreach ($files as $file) {
                if (false === file_exists($app->basePath() . $file)) {
                    warn("Route file cant be load '{$file}'.")->out();

                    return 1;
                }

                require $app->basePath() . $file;
            }
        }

        $routes = [];
        foreach ($router->getRoutesRaw() as $route) {
            $routes[] = [
                'method'     => $route['method'],
                'uri'        => $route['uri'],
                'expression' => $route['expression'],
                'function'   => $route['function'],
                'middleware' => $route['middleware'],
                'name'       => $route['name'],
                'patterns'   => $route['patterns'] ?? [],
            ];
        }

        $file = $app->getApplicationCachePath() . 'route.php';
        if ((new VarExport())->compile($routes, $file)) {
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
