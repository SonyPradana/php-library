<?php

namespace System\Test\Container\Fixtures;

class UnionTypeConstructorClass
{
    public function __construct(public DependencyClass|AnotherService $dep)
    {
    }
}
