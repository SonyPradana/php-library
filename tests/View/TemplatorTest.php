<?php

use PHPUnit\Framework\TestCase;
use System\View\Templator;

class TemplatorTest extends TestCase
{
    /** @test */
    public function itCanRenderPhpTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        $view = new Templator($loader, $cache);
        $out  = $view->render('php.php', []);

        $this->assertEquals('<html><head></head><body>taylor</body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderIncludeTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        $view = new Templator($loader, $cache);
        $out  = $view->render('include.php', []);

        $this->assertEquals('<html><head></head><body><p>taylor</p></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderNameTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        $view = new Templator($loader, $cache);
        $out  = $view->render('naming.php', ['name' => 'taylor', 'age' => 17]);

        $this->assertEquals('<html><head></head><body><h1>your taylor, ages 17 </h1></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderIfTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        $view = new Templator($loader, $cache);
        $out  = $view->render('if.php', ['true' => true]);

        $this->assertEquals('<html><head></head><body><h1> show </h1><h1></h1></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderEachTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        $view = new Templator($loader, $cache);
        $out  = $view->render('each.php', ['numbsers' => [1, 2, 3]]);

        $this->assertEquals('<html><head></head><body>123</body></html>', trim($out));
    }

}
