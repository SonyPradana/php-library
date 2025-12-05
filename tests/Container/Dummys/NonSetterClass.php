<?php

namespace System\Test\Container\Dummys;

class NonSetterClass
{
    public $called = false;

    public function doSomething(DependencyClass $dependency)
    {
        $this->called = true;
    }
}
