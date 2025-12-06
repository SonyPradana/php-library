<?php

namespace System\Test\Container\Fixtures;

class MultipleSetterClass
{
    public $dependency1;
    public $dependency2;

    public function setDependency1(DependencyClass $dependency)
    {
        $this->dependency1 = $dependency;
    }

    public function setDependency2(AnotherService $anotherService)
    {
        $this->dependency2 = $anotherService;
    }
}
