<?php

namespace System\Test\Container\Dummys;

class UnionTypeConstructorClass
{
    public function __construct(public DependencyClass|AnotherService $dep)
    {
    }
}
