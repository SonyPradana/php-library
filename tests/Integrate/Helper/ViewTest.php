<?php

declare(strict_types=1);

namespace System\Test\Integrate\Helper;

use PHPUnit\Framework\TestCase;
use System\Http\Response;
use System\Integrate\Application;
use System\Text\Str;
use System\View\Manifestor;
use System\View\Templator;

final class ViewTest extends TestCase
{
    public function testItCanGetResponeFromeContainer()
    {
        $app = new Application('/');

        (new Manifestor(__DIR__ . '/assets/view/', __DIR__ . '/assets/cache/'))->init();

        $app->set(
            'view.response',
            fn () => fn (string $view_path, array $portal = []): Response => (new Response())
                        ->setContent(
                            (new Templator(__DIR__ . '/assets/view/', __DIR__ . '/assets/cache'))
                                ->render($view_path, $portal)
                        )
        );

        $view = view('test.php', [], ['status' => 500]);
        $this->assertEquals(500, $view->getStatusCode());
        $this->assertTrue(
            Str::contains($view->getContent(), 'savanna')
        );

        $app->flush();
    }
}
