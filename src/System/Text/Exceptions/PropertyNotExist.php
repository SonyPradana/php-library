<?php

declare(strict_types=1);

namespace System\Text\Exceptions;

/**
 * @internal
 */
final class PropertyNotExist extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $property_name)
    {
        parent::__construct(sprintf('Property `%s` not exist.', $property_name));
    }
}
