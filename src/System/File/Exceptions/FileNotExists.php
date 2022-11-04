<?php

declare(strict_types=1);

namespace System\File\Exceptions;

/**
 * @internal
 */
final class FileNotExists extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $file_location)
    {
        parent::__construct(sprintf('File location not exists `%s`', $file_location));
    }
}
