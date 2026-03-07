<?php

namespace System\Test\Container\Fixtures;

class ClassWithMissingDependency
{
    public function __construct(UnresolvableInterface $dep)
    {
    }
}
