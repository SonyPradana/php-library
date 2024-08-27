<?php

declare(strict_types=1);

namespace System\Console\IO;

use System\Console\Interfaces\OutputStream;

/**
 * inspire by Aydin Hassan <aydin@hotmail.co.uk>.
 *
 * @source https://github.com/php-school/terminal/blob/master/src/IO/OutputStream.php
 */
class ResourceOutputStream implements OutputStream
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * ResourceOutputStream constructor.
     *
     * @param resource $stream
     *
     * @throws \InvalidArgumentException if the stream is not a valid or writable resource
     */
    public function __construct($stream = \STDOUT)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \InvalidArgumentException('Expected a valid stream');
        }

        $meta = stream_get_meta_data($stream);
        if (str_contains($meta['mode'], 'r') && !str_contains($meta['mode'], '+')) {
            throw new \InvalidArgumentException('Expected a writable stream');
        }

        $this->stream = $stream;
    }

    /**
     * Writes the buffer to the stream.
     *
     * @throws \InvalidArgumentException if writing to the stream fails
     */
    public function write(string $buffer): void
    {
        if (fwrite($this->stream, $buffer) === false) {
            throw new \InvalidArgumentException('Failed to write to stream');
        }
    }

    /**
     * Checks whether the stream is interactive (connected to a terminal).
     */
    public function isInteractive(): bool
    {
        return stream_isatty($this->stream);
    }
}
