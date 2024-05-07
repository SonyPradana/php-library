<?php

declare(strict_types=1);

namespace System\Test\Integrate\Helper;

use PHPUnit\Framework\TestCase;
use System\Http\Response;
use System\Integrate\Application;
use System\Text\Str;
use System\View\Templator;
use System\View\TemplatorFinder;

final class ViewTest extends TestCase
{
    public function testItCanGetResponeFromeContainer()
    {
        $app = new Application(__DIR__);

        $app->set(
            TemplatorFinder::class,
            fn () => new TemplatorFinder([__DIR__ . '/assets/view'], ['.php'])
        );

        $app->set(
            'view.instance',
            fn (TemplatorFinder $finder) => new Templator($finder, __DIR__ . '/assets/cache')
        );

        $app->set(
            'view.response',
            fn () => fn (string $view_path, array $portal = []): Response => new Response(
                $app->make(Templator::class)->render($view_path, $portal)
            )
        );

        $view = view('test', [], ['status' => 500]);
        $this->assertEquals(500, $view->getStatusCode());
        $this->assertTrue(
            Str::contains($view->getContent(), 'savanna')
        );

        $app->flush();
    }
}
