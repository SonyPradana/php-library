<?php

namespace System\Test\Container\Dummys;

class ClassWithMissingDependency
{
    public function __construct(UnresolvableInterface $dep)
    {
    }
}
