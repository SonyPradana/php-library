<?php

declare(strict_types=1);

namespace System\Container\Exceptions;

class CircularAliasException extends BindingResolutionException
{
    public function __construct(string $abstract)
    {
        parent::__construct("Circular alias reference detected for {$abstract}");
    }
}
