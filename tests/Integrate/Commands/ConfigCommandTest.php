<?php

declare(strict_types=1);

namespace System\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Integrate\Console\ConfigCommand;

class ConfigCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        // tests\Integrate\bootsrap\cache\config.php
        if (file_exists($file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootsrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.php')) {
            @unlink($file);
        }
    }

    /**
     * @test
     */
    public function itCanCreateConfigFile()
    {
        $app = new Application(dirname(__DIR__) . DIRECTORY_SEPARATOR);

        $app->setConfigPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR);

        $command = new ConfigCommand([]);

        ob_start();
        $status = $command->main();
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Config file has successfully created.', $out);

        $app->flush();
    }

    /**
     * @test
     */
    public function itCanRemoveConfigFile()
    {
        $app = new Application(dirname(__DIR__) . DIRECTORY_SEPARATOR);

        $app->setConfigPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR);

        $command = new ConfigCommand([]);

        ob_start();
        $command->main();
        $status = $command->clear();
        $out    = ob_get_clean();

        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Config file has successfully created.', $out);

        $app->flush();
    }
}
