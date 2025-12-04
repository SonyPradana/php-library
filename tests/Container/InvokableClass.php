<?php

declare(strict_types=1);

namespace System\Test\Container;

class InvokableClass
{
    public function __invoke(): string
    {
        return 'invoked';
    }
}
