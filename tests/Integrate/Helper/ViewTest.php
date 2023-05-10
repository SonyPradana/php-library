<?php

declare(strict_types=1);

namespace System\Test\Integrate\Helper;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Router\Controller;
use System\Text\Str;
use System\View\Templator;

final class ViewTest extends TestCase
{
    public function testItCanGetResponeFromeContainer()
    {
        new Application('/');

        $controller = new class() extends Controller {
            public static function renderView(string $view_path, array $portal = [])
            {
                return (new Templator(__DIR__ . '/assets/view/', __DIR__ . '/assets/cache'))->render($view_path, $portal);
            }
        };

        app()->set('view.response', fn () => new $controller());

        $view = view('test.php', []);
        $this->assertEquals(200, $view->getStatusCode());
        $this->assertTrue(
            Str::contains($view->getContent(), 'savanna')
        );
    }
}
