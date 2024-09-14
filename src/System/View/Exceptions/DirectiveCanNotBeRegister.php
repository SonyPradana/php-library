<?php

declare(strict_types=1);

namespace System\View\Exceptions;

/**
 * @internal
 */
final class DirectiveCanNotBeRegister extends \InvalidArgumentException
{
    public function __construct(string $name, string $use_by)
    {
        parent::__construct("Directive '$name' cant be use, this has been use in '$use_by'.");
    }
}
