<?php

declare(strict_types=1);

namespace System\Test\Console\IO;

use PHPUnit\Framework\TestCase;
use System\Console\IO\ResourceOutputStream;

class ResourceOutputStreamTest extends TestCase
{
    /**
     * Test constructing the ResourceOutputStream with valid stream.
     */
    public function testConstructorWithValidStream(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);

        $this->assertInstanceOf(ResourceOutputStream::class, $outputStream);
        fclose($stream);
    }

    /**
     * Test constructor throws exception for invalid stream.
     */
    public function testConstructorThrowsForInvalidStream(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a valid stream');

        new ResourceOutputStream('invalid_stream');
    }

    /**
     * Test constructor throws exception for non-writable stream.
     */
    public function testConstructorThrowsForNonWritableStream(): void
    {
        $stream = fopen('php://memory', 'r');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a writable stream');

        new ResourceOutputStream($stream);

        fclose($stream);
    }

    /**
     * Test writing to a valid stream.
     */
    public function testWriteToStream(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);

        $outputStream->write('Hello, World!');

        rewind($stream);
        $this->assertEquals('Hello, World!', stream_get_contents($stream));

        fclose($stream);
    }

    /**
     * Test if the stream is interactive.
     */
    public function testIsInteractive(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);

        $this->assertFalse($outputStream->isInteractive());

        fclose($stream);
    }
}
