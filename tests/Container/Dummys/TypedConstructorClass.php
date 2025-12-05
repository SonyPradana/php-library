<?php

namespace System\Test\Container\Dummys;

class TypedConstructorClass
{
    public function __construct(public DependencyClass $dep)
    {
    }
}
