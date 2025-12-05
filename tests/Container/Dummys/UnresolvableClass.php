<?php

namespace System\Test\Container\Dummys;

class UnresolvableClass
{
    public function __construct(UnresolvableInterface $dependency)
    {
    }
}
