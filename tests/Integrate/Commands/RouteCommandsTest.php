<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Console\RouteCommand;
use System\Router\Router;

final class RouteCommandsTest extends TestCommand
{
    /**
     * @test
     */
    public function itCanRenderRouteWithSomeRouter()
    {
        Router::get('/test', fn () => '');
        Router::post('/post', fn () => '');

        $route_command = new RouteCommand($this->argv('cli route:list'));
        ob_start();
        $exit = $route_command->main();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('GET', $out);
        $this->assertContain('/test', $out);

        Router::Reset();
    }
}
