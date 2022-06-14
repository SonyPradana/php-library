<?php

use PHPUnit\Framework\TestCase;
use System\View\Exceptions\ViewFileNotFound;
use System\View\View;

class RenderViewTest extends TestCase
{
    /** @test */
    public function itCanRenderUsingViewClasses(): void
    {
        $test_html  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'sample.html';
        $test_php   = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'sample.php';

        ob_start();
        View::render($test_html)->send();
        $render_html = ob_get_clean();

        ob_start();
        View::render($test_php, ['contents' => ['say' => 'hay']])->send();
        $render_php = ob_get_clean();

        // view: view-html
        $this->assertEquals(
            "<html><head></head><body></body></html>\n",
            $render_html,
            'it must same output with template html'
        );

        // view: view-php
        $this->assertEquals(
            "<html><head></head><body><h1>hay</h1></body></html>\n",
            $render_php,
            'it must same output with template html'
        );
    }

    /** @test */
    public function itThrowWhenFileNotFound()
    {
        $this->expectException(ViewFileNotFound::class);
        View::render('unknow');
    }
}
