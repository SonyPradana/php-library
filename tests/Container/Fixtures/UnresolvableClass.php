<?php

namespace System\Test\Container\Fixtures;

class UnresolvableClass
{
    public function __construct(UnresolvableInterface $dependency)
    {
    }
}
