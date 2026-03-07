<?php

namespace System\Test\Container\Fixtures;

class UnresolvableSetterClass
{
    public $dependency;

    public function setUnresolvable(UnresolvableInterface $dependency)
    {
        $this->dependency = $dependency;
    }
}
