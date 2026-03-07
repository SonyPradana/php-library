<?php

namespace System\Test\Container\Fixtures;

class Dependant
{
    public function __construct(public Dependency $dep)
    {
    }
}
