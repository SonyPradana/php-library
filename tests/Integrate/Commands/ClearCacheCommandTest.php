<?php

declare(strict_types=1);

namespace System\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Cache\CacheManager;
use System\Cache\Storage\ArrayStorage;
use System\Integrate\Application;
use System\Integrate\Console\ClearCacheCommand;

class ClearCacheCommandTest extends TestCase
{
    private ?Application $app = null;

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__);
    }

    protected function teardown(): void
    {
        $this->app->flush();
        $this->app = null;
    }

    /** @test */
    public function itCantRunCommand(): void
    {
        $command = new ClearCacheCommand(['cli', 'clear:cache']);

        ob_start();
        $code = $command->clear($this->app);
        $out  =ob_get_clean();

        $this->assertEquals(1, $code);
        $this->assertStringContainsString('Cache is not set yet.', $out);
    }

    /** @test */
    public function itCanClearDefaultDriver(): void
    {
        $this->app->set('cache', fn () => new CacheManager());
        $command = new ClearCacheCommand(['cli', 'clear:cache']);

        ob_start();
        $code = $command->clear($this->app);
        $out  =ob_get_clean();

        $this->assertEquals(0, $code);
        $this->assertStringContainsString('Done default cache driver has been clear.', $out);
    }

    /** @test */
    public function itCanAllDriver(): void
    {
        $cache_manager = new CacheManager();
        $cache_manager->setDriver('array', new ArrayStorage());
        $this->app->set('cache', fn () => $cache_manager);
        $command = new ClearCacheCommand(['cli', 'clear:cache', '--all'], ['all' => true]);

        ob_start();
        $code = $command->clear($this->app);
        $out  =ob_get_clean();

        $this->assertEquals(0, $code);
        $this->assertStringContainsString("clear 'array' driver.", $out);
    }

    /** @test */
    public function itCanSpesifikDriver(): void
    {
        $cache_manager = new CacheManager();
        $cache_manager->setDriver('array', new ArrayStorage());
        $this->app->set('cache', fn () => $cache_manager);
        $command = new ClearCacheCommand(['cli', 'clear:cache', '--drivers array'], ['drivers' => 'array']);

        ob_start();
        $code = $command->clear($this->app);
        $out  =ob_get_clean();

        $this->assertEquals(0, $code);
        $this->assertStringContainsString("clear 'array' driver.", $out);
    }
}
