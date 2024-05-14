<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use System\Integrate\Console\SeedCommand;

final class SeedCommandsTest extends TestCommand
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $migration = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR;
        array_map('unlink', glob("{$migration}/*.php"));
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandSeederWithSuccess()
    {
        $makeCommand = new SeedCommand($this->argv('cli make:seed BaseSeeder'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/seeders/BaseSeeder.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class BaseSeeder extends Seeder', $class, 'Stub test');
        $this->assertContain('public function run(): void', $class, 'Stub test');
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandSeedWithFails()
    {
        $makeCommand = new SeedCommand($this->argv('cli make:seed'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * @test
     */
    public function itCanCallMakeCommandSeedWithFailsFileExist()
    {
        app()->setSeederPath(__DIR__ . '//database//seeders//');
        $makeCommand = new SeedCommand($this->argv('cli make:seed BasicSeeder'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * @test
     */
    public function itCanCallMakeExistCommandSeeder()
    {
        app()->setSeederPath(__DIR__ . '//database//seeders//');
        $makeCommand = new SeedCommand($this->argv('cli make:seed ExistSeeder --force'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '//database//seeders//ExistSeeder.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class ExistSeeder extends Seeder', $class, 'Stub test');
        $this->assertContain('public function run(): void', $class, 'Stub test');
    }
}
