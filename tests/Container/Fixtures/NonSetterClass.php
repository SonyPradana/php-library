<?php

namespace System\Test\Container\Fixtures;

class NonSetterClass
{
    public $called = false;

    public function doSomething(DependencyClass $dependency)
    {
        $this->called = true;
    }
}
