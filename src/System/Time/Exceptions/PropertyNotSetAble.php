<?php

declare(strict_types=1);

namespace System\Time\Exceptions;

/**
 * @internal
 */
final class PropertyNotSetAble extends \InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     */
    public function __construct(string $property_name)
    {
        parent::__construct(sprintf('Property `%s` not set able.', $property_name));
    }
}
