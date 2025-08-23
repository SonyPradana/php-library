<?php

declare(strict_types=1);

namespace System\Console\IO;

use System\Console\Interfaces\OutputStream;

/**
 * inspire by Symfony Console Component.
 *
 * @source https://github.com/symfony/symfony/blob/5.3/src/Symfony/Component/Console/Output/BufferedOutput.php
 */
class BufferedOutputStream implements OutputStream
{
    private string $buffer = '';

    /**
     * Empties buffer and returns its content.
     */
    public function fetch(): string
    {
        $content      = $this->buffer;
        $this->buffer = '';

        return $content;
    }

    /**
     * Writes the buffer to the stream.
     */
    public function write(string $message): void
    {
        $this->buffer .= $message;
    }

    /**
     * Checks whether the stream is interactive (connected to a terminal).
     */
    public function isInteractive(): bool
    {
        return false;
    }
}
