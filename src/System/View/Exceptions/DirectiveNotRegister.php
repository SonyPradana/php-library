<?php

declare(strict_types=1);

namespace System\View\Exceptions;

/**
 * @internal
 */
final class DirectiveNotRegister extends \InvalidArgumentException
{
    public function __construct(string $name)
    {
        parent::__construct("Directive '$name' is not registered.");
    }
}
