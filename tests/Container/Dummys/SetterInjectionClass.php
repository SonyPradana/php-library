<?php

namespace System\Test\Container\Dummys;

class SetterInjectionClass
{
    public $dependency;

    public function setDependency(DependencyClass $dependency)
    {
        $this->dependency = $dependency;
    }
}
