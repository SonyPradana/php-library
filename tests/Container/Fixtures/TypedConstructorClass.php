<?php

namespace System\Test\Container\Fixtures;

class TypedConstructorClass
{
    public function __construct(public DependencyClass $dep)
    {
    }
}
