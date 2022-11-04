<?php

declare(strict_types=1);

namespace System\View\Exceptions;

/**
 * @internal
 */
final class ViewFileNotFound extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $file_name)
    {
        parent::__construct(sprintf('View path not exists `%s`', $file_name));
    }
}
