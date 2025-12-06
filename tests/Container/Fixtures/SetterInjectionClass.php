<?php

namespace System\Test\Container\Fixtures;

class SetterInjectionClass
{
    public $dependency;

    public function setDependency(DependencyClass $dependency)
    {
        $this->dependency = $dependency;
    }
}
