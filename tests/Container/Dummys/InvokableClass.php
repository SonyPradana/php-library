<?php

declare(strict_types=1);

namespace System\Test\Container\Dummys;

class InvokableClass
{
    public function __invoke(): string
    {
        return 'invoked';
    }
}
