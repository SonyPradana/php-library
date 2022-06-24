<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\File\Exceptions\FolderNotExists;
use System\File\Exceptions\FolederNotExists;
use System\File\UploadFile;
use System\File\UploadMultyFile;

final class UploadFileTest extends TestCase
{
    private $files = [
        'file_1' => [
            'name'      => 'test123.txt',
            'type'      => 'file',
            'tmp_name'  => __DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test123.tmp',
            'error'     => 0,
            'size'      => 1,
        ],
        'file_2' => [
            'name'      => ['test123.txt', 'test234.txt'],
            'type'      => ['file', 'file'],
            'tmp_name'  => [
                __DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test123.tmp',
                __DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'test234.tmp',
            ],
            'error'     => [0, 0],
            'size'      => [1, 1],
        ],
    ];

    /** @var UploadFile */
    private $upload;

    protected function setUp(): void
    {
        if (!ini_get('file_uploads')) {
            $this->markTestSkipped('file_uploads is disabled in php.ini');
        }

        $this->files['file_1']['size'] = filesize($this->files['file_1']['tmp_name']);
        $this->files['file_1']['type'] = filetype($this->files['file_1']['tmp_name']);

        $this->upload = new UploadFile($this->files['file_1']);
        $this->upload
            ->markTest(true)
            ->setFileName('success')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file']);
    }

    protected function tearDown(): void
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'success.txt';
        if (file_exists($file)) {
            unlink($file);
        }

        $this->upload = null;
    }

    /** @test */
    public function itCanUploadFileValid()
    {
        $this->upload->upload();

        $this->assertTrue($this->upload->success());
        $this->assertEquals('success', $this->upload->getError());
        $this->assertEquals('This is a story about something that happened long ago when your grandfather was a child.', trim($this->upload->get()));
    }

    /** @test */
    public function itCanUploadFileInvalidFileType()
    {
        $this->upload->setFileTypes(['md'])->upload();

        $this->assertFalse($this->upload->success());
    }

    /** @test */
    public function itCantUploadFileInvalidFileFolder()
    {
        // $this->expectException(FolederNotExists::class);
        $this->expectException(FolderNotExists::class);

        $this->upload->setFolderLocation('/unkow');
    }

    /** @test */
    public function itCanUploadFileInvalidFileSize()
    {
        $this->upload->setMaxFileSize(89)->upload();

        $this->assertFalse($this->upload->success());
    }

    /** @test */
    public function itCanUploadFileInvalidMime()
    {
        $this->upload->setMimeTypes(['image/jpeg'])->upload();

        $this->assertFalse($this->upload->success());
    }

    /** @test */
    public function itCanUploadFileInvalidNofileUpload()
    {
        $this->files['file_1']['error'] = 4;

        $upload = new UploadFile($this->files['file_1']);
        $upload
            ->markTest(true)
            ->setFileName('success')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file']);

        $this->assertFalse($upload->success());

        // reset
        $this->files['file_1']['error'] = 0;
    }

    /** @test */
    public function itCanMultyUploadFileButSingleFile()
    {
        $upload = new UploadMultyFile($this->files['file_2']);
        $upload
            ->markTest(true)
            ->setFileName('multy_file_')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file'])
            ->uploads();

        $this->assertTrue($upload->success());
        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file_0.txt');
        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file_1.txt');

        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file_0.txt');
        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file_1.txt');
    }

    // /** @test */
    public function itCanMultyUploadFile()
    {
        $upload = new UploadMultyFile($this->files['file_2']);
        $upload
            ->markTest(true)
            ->setFileName('multy_file')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file'])
            ->uploads();

        $this->assertTrue($upload->success());
        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file_0.txt');
        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file_1.txt');

        unlink(__DIR__ . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'multy_file.txt');
    }
}
