<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Integrate\Console\VendorImportCommand;

final class VendorImportCommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(__DIR__ . '/assets/copy/to/file.txt');
    }

    /**
     * @test
     */
    public function itCanExportModule(): void
    {
        VendorImportCommand::export([
            '/vendor/package/database/' => '/database/',
        ]);

        VendorImportCommand::export([
            '/vendor/package/resource/view/' => '/resourve/view/',
        ], 'pacakge-share');

        VendorImportCommand::export([
            '/vendor/package/resource/js/app.js' => '/resourve/js/app.js',
        ], 'pacakge-share');

        $this->assertEquals([
            ''              => ['/vendor/package/database/' => '/database/'],
            'pacakge-share' => [
                '/vendor/package/resource/view/'     => '/resourve/view/',
                '/vendor/package/resource/js/app.js' => '/resourve/js/app.js',
            ],
        ], VendorImportCommand::getModules());
    }

    /**
     * @test
     */
    public function itCanGetModule(): void
    {
        VendorImportCommand::export([
            '/vendor/package/database/' => '/database/',
        ]);
        VendorImportCommand::flushModule();

        $this->assertEquals([], VendorImportCommand::getModules());
    }

    /**
     * @test
     */
    public function itCanImportFile(): void
    {
        $this->assertTrue(VendorImportCommand::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFileWithFolderDoestExits(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(VendorImportCommand::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFileWithTargetExist(): void
    {
        file_put_contents(__DIR__ . '/assets/copy/to/file.txt', '');

        $this->assertTrue(VendorImportCommand::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt',
            true
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * @test
     */
    public function itCanNotImportFileWithTargetExist(): void
    {
        file_put_contents(__DIR__ . '/assets/copy/to/file.txt', '');

        $this->assertFalse(VendorImportCommand::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFolder(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(VendorImportCommand::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFolderRecursing(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(VendorImportCommand::importDir(
            __DIR__ . '/assets/copy/from/folder-nest',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/folder/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFolderWithTargetExist(): void
    {
        $this->assertTrue(VendorImportCommand::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folder',
            true
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folder/file.txt'));
    }

    /**
     * @test
     */
    public function itCaNotImportFolderWithTargetExist(): void
    {
        $this->assertFalse(VendorImportCommand::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folder'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folder/file.txt'));
    }
}
