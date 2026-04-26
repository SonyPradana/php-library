<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\ConfigRepository;
use System\Integrate\Console\ViewCommand;
use System\View\Templator;
use System\View\TemplatorFinder;

final class ViewCommandsTest extends TestCase
{
    /**
     * @test
     */
    public function itCanCompileFromTemplatorFiles(): void
    {
        $app = new Application(__DIR__);

        $app->setCompiledViewPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view_cache' . DIRECTORY_SEPARATOR);
        $app->setViewPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);

        $app->set(
            TemplatorFinder::class,
            fn () => new TemplatorFinder([view_path()], ['.php', ''])
        );

        $app->set(
            'view.instance',
            fn (TemplatorFinder $finder) => new Templator($finder, $app->compiled_view_path())
        );

        $view_command = new ViewCommand(['php', 'cli', 'view:cache'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $view_command->cache($app->make(Templator::class));
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertFileExists(compiled_view_path() . md5('test.php') . '.php');
    }

    /**
     * @test
     */
    public function itCanClearCompiledViewFile(): void
    {
        // tests\Integrate\Commands\assets\view_cache
        $app = new Application('');
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        $app->setCompiledViewPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view_cache' . DIRECTORY_SEPARATOR);

        file_put_contents(compiled_view_path() . 'test01.php', '');
        file_put_contents(compiled_view_path() . 'test01.dep', '');
        file_put_contents(compiled_view_path() . 'test02.php', '');
        $view_command = new ViewCommand(['php', 'cli', 'view:clear'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $view_command->clear();
        ob_get_clean();
        $this->assertEquals(0, $exit);
        $this->assertFileDoesNotExist(compiled_view_path() . 'test01.php');
        $this->assertFileDoesNotExist(compiled_view_path() . 'test01.dep');
        $this->assertFileDoesNotExist(compiled_view_path() . 'test02.php');
    }
}
