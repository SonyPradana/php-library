<?php

use PHPUnit\Framework\TestCase;
use System\Text\Str;
use System\View\Manifestor;
use System\View\Templator;

class TemplatorTest extends TestCase
{
    protected function tearDown(): void
    {
        $files = glob(__DIR__ . '/caches/*.php');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $manifests = glob(__DIR__ . '/caches/*.json');
        foreach ($manifests as $manifest) {
            if (is_file($manifest)) {
                unlink($manifest);
            }
        }
        $manifests = glob(__DIR__ . '/caches_fixed/*.json');
        foreach ($manifests as $manifest) {
            if (is_file($manifest)) {
                unlink($manifest);
            }
        }
    }

    private function assertSee(string $text, string $find)
    {
        $this->assertTrue(Str::contains($text, $find));
    }

    private function assertBlind(string $text, string $find)
    {
        $this->assertTrue(!Str::contains($text, $find));
    }

    /** @test */
    public function itCanRenderPhpTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('php.php', []);

        $this->assertEquals('<html><head></head><body>taylor</body></html>', trim($out));

        // without cache
        $out  = $view->render('php.php', [], false);
        $this->assertEquals('<html><head></head><body>taylor</body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderIncludeTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('include.php', []);

        $this->assertSee(trim($out), '<p>taylor</p>');

        // without cache
        $out  = $view->render('include.php', [], false);
        $this->assertSee(trim($out), '<p>taylor</p>');
    }

    /** @test */
    public function itCanRenderIncludeNestingTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('nesting.include.php', []);

        $this->assertSee(trim($out), '<p>taylor</p>');

        // without cache
        $out  = $view->render('nesting.include.php', [], false);
        $this->assertSee(trim($out), '<p>taylor</p>');
    }

    /** @test */
    public function itCanRenderNameTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('naming.php', ['name' => 'taylor', 'age' => 17]);

        $this->assertEquals('<html><head></head><body><h1>your taylor, ages 17 </h1></body></html>', trim($out));

        // without cache
        $out  = $view->render('naming.php', ['name' => 'taylor', 'age' => 17], false);
        $this->assertEquals('<html><head></head><body><h1>your taylor, ages 17 </h1></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderNameTemplateWithTernary(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('naming-ternary.php', ['age' => false]);

        $this->assertEquals('<html><head></head><body><h1>your nuno, ages 28 </h1></body></html>', trim($out));

        // without cache
        $out  = $view->render('naming-ternary.php', ['age' => false], false);
        $this->assertEquals('<html><head></head><body><h1>your nuno, ages 28 </h1></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderNameTemplateInSubFolder(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('Groups/nesting.php', ['name' => 'taylor', 'age' => 17]);

        $this->assertEquals('<html><head></head><body><h1>your taylor, ages 17 </h1></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderIfTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('if.php', ['true' => true]);

        $this->assertEquals('<html><head></head><body><h1> show </h1><h1></h1></body></html>', trim($out));

        // without cache
        $out  = $view->render('if.php', ['true' => true], false);
        $this->assertEquals('<html><head></head><body><h1> show </h1><h1></h1></body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderElseIfTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('else.php', ['true' => false]);

        $this->assertEquals('<html><head></head><body><h1> hide </body></html>', trim($out));

        // without cache
        $out  = $view->render('else.php', ['true' => false], false);
        $this->assertEquals('<html><head></head><body><h1> hide </body></html>', trim($out));
    }

    /** @test */
    public function itCanRenderEachTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('each.php', ['numbsers' => [1, 2, 3]]);

        $this->assertEquals('<html><head></head><body>123</body></html>', trim($out));

        // without cache
        $out  = $view->render('each.php', ['numbsers' => [1, 2, 3]], false);
        $this->assertEquals('<html><head></head><body>123</body></html>', trim($out));
    }

    /**
     * @test
     */
    public function itCanRenderSectionTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('slot.php', [
            'title'   => 'taylor otwell',
            'product' => 'laravel',
            'year'    => 2023,
        ]);

        $this->assertSee($out, 'taylor otwell');
        $this->assertSee($out, 'laravel');
        $this->assertSee($out, 2023);
    }

    /**
     * @test
     */
    public function itCanThrowErrorSectionTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);

        try {
            $view->render('slot_miss.php', [
                'title'   => 'taylor otwell',
                'product' => 'laravel',
                'year'    => 2023,
            ]);
        } catch (\Throwable $th) {
            $this->assertEquals("Slot with extends 'Slots/layout.php' required 'title'", $th->getMessage());
        }
    }

    /**
     * @test
     */
    public function itCanRenderTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view         = new Templator($loader, $cache);
        $view->suffix = '.php';
        $out          = $view->render('portofolio', [
            'title'    => 'cool portofolio',
            'products' => ['laravel', 'forge'],
        ]);

        $this->assertSee($out, 'cool portofolio');
        $this->assertSee($out, 'taylor');
        $this->assertSee($out, 'laravel');
        $this->assertSee($out, 'forge');
    }

    /** @test */
    public function itCanRenderCommentTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('comment.php', []);

        $this->assertBlind($out, 'this a comment');

        // without cache
        $out  = $view->render('comment.php', [], false);
        $this->assertBlind($out, 'this a comment');
    }

    /** @test */
    public function itCanRenderRepeatTemplate(): void
    {
        $loader  = __DIR__ . DIRECTORY_SEPARATOR . 'sample' . DIRECTORY_SEPARATOR . 'Templators';
        $cache   = __DIR__ . DIRECTORY_SEPARATOR . 'caches';

        (new Manifestor($loader, $cache, '/manifest.json'))->init();

        $view = new Templator($loader, $cache);
        $out  = $view->render('repeat.include.php', []);

        $this->assertEquals(6, substr_count($out, 'some text'));

        // without cache
        $out  = $view->render('repeat.include.php', [], false);
        $this->assertEquals(6, substr_count($out, 'some text'));
    }

    /**
     * @test
     */
    public function itCheckDepencyIsUpdate()
    {
        $manifest = new Manifestor(
            __DIR__ . '/caches_fixed',          // templateDir
            __DIR__ . '/caches/',               // cache_path
            'manifestor.dependency.test.json'   // manifest_name
        );
        $templator = new Templator(
            __DIR__ . '/caches_fixed',           // templateDir
            __DIR__ . '/caches/',               // cache_path
            'manifestor.dependency.test.json'   // manifest_name
        );

        $template_name = 'include.php';
        $cache_name    = md5($template_name) . '.php';
        $manifest->putManifest([
            $cache_name => ['include2.php', 'include3.php'],
        ]);

        file_put_contents(__DIR__ . '/caches/' . $cache_name, 'no render required');
        $out = $templator->render($template_name, []);

        $this->assertTrue($manifest->isDependencyUptodate($cache_name));
        $this->assertTrue(Str::contains($out, 'no render required'));
    }

    /**
     * @test
     */
    public function itCheckDepencyIsNotUpdate()
    {
        $manifest = new Manifestor(
            __DIR__ . '/caches_fixed',          // templateDir
            __DIR__ . '/caches/',               // cache_path
            'manifestor.dependency.test.json'   // manifest_name
        );
        $templator = new Templator(
            __DIR__ . '/caches_fixed',           // templateDir
            __DIR__ . '/caches/',               // cache_path
            'manifestor.dependency.test.json'   // manifest_name
        );

        $template_name = 'new_file.php';
        $cache_name    = md5($template_name) . '.php';
        $manifest->putManifest([
            $cache_name => ['new_file.php'],
        ]);

        file_put_contents(__DIR__ . '/caches_fixed/new_file.php', 'rerender required');
        file_put_contents(__DIR__ . '/caches/' . $cache_name, 'no render required');
        $out = $templator->render($template_name, []);

        $this->assertFalse($manifest->isDependencyUptodate($cache_name));
        $this->assertTrue(Str::contains($out, 'rerender required'));
    }
}
