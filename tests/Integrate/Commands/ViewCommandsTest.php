<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\Console\ViewCommand;

final class ViewCommandsTest extends TestCase
{
    /**
     * @test
     */
    public function itCanCompileFromTemplatorFiles(): void
    {
        $this->markTestIncomplete('Wait new PR for handle tempaltor::compailer.');
        $view_command = new ViewCommand(['php', 'cli', 'view:cache']);
        ob_start();
        $exit = $view_command->cache();
        ob_get_clean();
        $this->assertEquals(0, $exit);
    }

    /**
     * @test
     */
    public function itCanClearCompiledViewFile(): void
    {
        // tests\Integrate\Commands\assets\view_cache
        (new Application(''))->setCachePath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view_cache' . DIRECTORY_SEPARATOR);

        file_put_contents(cache_path() . 'test01.php', '');
        file_put_contents(cache_path() . 'test02.php', '');
        $view_command = new ViewCommand(['php', 'cli', 'view:clear']);
        ob_start();
        $exit = $view_command->clear();
        ob_get_clean();
        $this->assertEquals(0, $exit);
    }
}
