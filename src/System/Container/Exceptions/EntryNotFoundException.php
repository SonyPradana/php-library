<?php

declare(strict_types=1);

namespace System\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct(string $name)
    {
        parent::__construct("No entry was found for '{$name}' identifier.");
    }
}
