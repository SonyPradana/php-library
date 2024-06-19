<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Console\VendorImportCommand;
use System\Integrate\ServiceProvider;

final class VendorImportCommandsTest extends TestCase
{
    private ?string $base_path;

    protected function setUp(): void
    {
        $this->base_path = dirname(__DIR__);
    }

    protected function tearDown(): void
    {
        ServiceProvider::flushModule();
        @unlink($this->base_path . '/assets/copy/to/file.txt');
    }

    /**
     * @test
     */
    public function itCanImport(): void
    {
        $publish = new VendorImportCommand(['cli', 'vendor:import', '--tag=test'], [
            'force' => false,
        ]);
        $random = now()->format('YmdHis') . microtime();

        ServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/file.txt' => $this->base_path . '/assets/copy/to/file.txt'],
            tag: 'test'
        );
        ServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/folder' => $this->base_path . '/assets/copy/to/folders/folder-' . $random],
            tag: 'test'
        );

        ob_start();
        $exit = $publish->main();
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(file_exists($this->base_path . '/assets/copy/to/file.txt'));
        $this->assertTrue(file_exists($this->base_path . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportWithTag(): void
    {
        $publish = new VendorImportCommand(['cli', 'vendor:import', '--tag=test'], [
            'force' => false,
            'tag'   => 'test',
        ]);
        $random = now()->format('YmdHis') . microtime();

        ServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/file.txt' => $this->base_path . '/assets/copy/to/file.txt'],
            tag: 'test'
        );
        ServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/folder' => $this->base_path . '/assets/copy/to/folders/folder-' . $random],
            tag: 'vendor'
        );

        ob_start();
        $exit = $publish->main();
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(file_exists($this->base_path . '/assets/copy/to/file.txt'));
        $this->assertFalse(file_exists($this->base_path . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }
}
