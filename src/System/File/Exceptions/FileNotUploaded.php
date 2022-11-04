<?php

declare(strict_types=1);

namespace System\File\Exceptions;

/**
 * @internal
 */
final class FileNotUploaded extends \RuntimeException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct()
    {
        parent::__construct('File not uploaded `%s`');
    }
}
