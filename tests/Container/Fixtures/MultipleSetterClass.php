<?php

namespace System\Test\Container\Fixtures;

use System\Container\Attribute\Inject;

class MultipleSetterClass
{
    public $dependency1;
    public $dependency2;

    #[Inject]
    public function setDependency1(DependencyClass $dependency)
    {
        $this->dependency1 = $dependency;
    }

    #[Inject]
    public function setDependency2(AnotherService $anotherService)
    {
        $this->dependency2 = $anotherService;
    }
}
