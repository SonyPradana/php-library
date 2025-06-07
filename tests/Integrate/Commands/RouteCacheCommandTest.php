<?php

declare(strict_types=1);

namespace System\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\Console\RouteCacheCommand;
use System\Router\Router;

class RouteCacheCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Router::Reset();
        if (file_exists($file = dirname(__DIR__) . '/assets/app1/bootstrap/cache/route.php')) {
            @unlink($file);
        }
    }

    private function createRouter(): Router
    {
        $route = new Router();
        $route->get('/test', [__CLASS__, __FUNCTION__])->name('test')->middleware(['test']);
        $route->get('/test/(:id)', [__CLASS__, 'empty']);
        $route->prefix('test/')->group(function () use ($route) {
            $route->post('/test/post', [__CLASS__, 'post'])->name('post');
        });

        return $route;
    }

    /**
     * @test
     */
    public function itCanCreateRouteCache(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');
        $app->setConfigPath('/config/');

        $command = new RouteCacheCommand([]);

        ob_start();
        $status = $command->cache($app, $this->createRouter());
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Route file has successfully created.', $out);
        $this->assertNotEmpty(Router::getRoutes());

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanCreateRouteCacheFromFiles(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');
        $app->setConfigPath('/config/');

        $command = new RouteCacheCommand(
            argv: [],
            default_option: [
                'files' => [['/routes/web.php']],
            ]
        );
        Router::Reset();

        ob_start();
        $status = $command->cache($app, new Router());
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Route file has successfully created.', $out);
        $this->assertNotEmpty(Router::getRoutes());

        $app->flush();
    }

    /**
     * @test
     */
    public function itFailCreateRouteCacheFromFiles(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');
        $app->setConfigPath('/config/');

        $command = new RouteCacheCommand(
            argv: [],
            default_option: [
                'files' => [['/routes/api.php']],
            ]
        );
        Router::Reset();

        ob_start();
        $status = $command->cache($app, new Router());
        $out    = ob_get_clean();

        $this->assertEquals(1, $status);
        $this->assertStringContainsString('Route file cant be load \'/routes/api.php\'.', $out);

        $app->flush();
    }

    /**
     * @test
     */
    public function itFailCreateRouteCache(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');
        $app->setConfigPath('/config/');

        $command = new RouteCacheCommand([]);
        $route   = $this->createRouter();
        $route->get('/fuction_not_allowed', fn () => 'not allowed')->name('function');

        ob_start();
        $status = $command->cache($app, $route);
        $out    = ob_get_clean();

        $this->assertEquals(1, $status);
        $this->assertStringContainsString('Route \'function\' cannot be cached because it contains a closure/callback function', $out);

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanGenerateValidRouterCache(): void
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');
        $app->setConfigPath('/config/');

        $command = new RouteCacheCommand([]);

        ob_start();
        $status = $command->cache($app, $this->createRouter());
        ob_get_clean();

        $cache_route = require_once dirname(__DIR__) . '/assets/app1/bootstrap/cache/route.php';
        foreach ($cache_route as $route) {
            Router::addRoutes($route);
        }

        $this->assertEquals(0, $status);
        $this->assertNotEmpty(Router::getRoutes());

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanRemoveConfigFile()
    {
        $app = new Application(dirname(__DIR__) . '/assets/app1/');
        $app->setConfigPath('/config/');

        $command = new RouteCacheCommand([]);

        ob_start();
        $command->cache($app, $this->createRouter());
        $status = $command->clear($app);
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Route file has successfully created.', $out);

        $app->flush();
    }
}
