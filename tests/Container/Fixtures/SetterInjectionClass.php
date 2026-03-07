<?php

namespace System\Test\Container\Fixtures;

use System\Container\Attribute\Inject;

class SetterInjectionClass
{
    public $dependency;

    #[Inject]
    public function setDependency(DependencyClass $dependency)
    {
        $this->dependency = $dependency;
    }
}
