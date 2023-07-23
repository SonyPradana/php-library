<?php

declare(strict_types=1);

namespace System\Router\Exceptions;

/**
 * @internal
 */
final class MethodNotExist extends \Exception
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $class_name, string $method)
    {
        parent::__construct("Resourece {$class_name}::{$method} does't exist.");
    }
}
