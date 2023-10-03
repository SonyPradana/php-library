<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use System\Integrate\Application;
use System\Text\Str;

class CommandTest extends TestCase
{
    protected ?Application $app;

    protected function setUp(): void
    {
        $this->app = new Application('');

        $this->app->setViewPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        $this->app->setContollerPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        $this->app->setServicesPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        $this->app->setModelPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        $this->app->setCommandPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        $this->app->setConfigPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
        $this->app->setMigrationPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR);
        $this->app->setSeederPath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR);
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        $this->app = null;
    }

    /**
     * @return string[]
     */
    protected function argv(string $argv)
    {
        return explode(' ', $argv);
    }

    protected function assertSuccess(int $code): void
    {
        Assert::assertEquals(0, $code, 'Command exit with success code');
    }

    protected function assertFails(int $code): void
    {
        Assert::assertGreaterThan(0, $code, 'Command exit with fail code');
    }

    public function testAlwaysTrue()
    {
        $this->assertSuccess(0);
    }

    public function assertContain(string $contain, string $in)
    {
        Assert::assertTrue(Str::contains($in, $contain), "This {$contain} is contain in {$in}.");
    }
}
