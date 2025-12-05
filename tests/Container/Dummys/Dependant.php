<?php

namespace System\Test\Container\Dummys;

class Dependant
{
    public function __construct(public Dependency $dep)
    {
    }
}
