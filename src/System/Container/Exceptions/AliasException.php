<?php

declare(strict_types=1);

namespace System\Container\Exceptions;

class AliasException extends BindingResolutionException
{
    public function __construct(string $abstract)
    {
        parent::__construct("{$abstract} is aliased to itself.");
    }
}
