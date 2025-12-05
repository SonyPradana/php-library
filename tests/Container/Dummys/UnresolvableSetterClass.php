<?php

namespace System\Test\Container\Dummys;

class UnresolvableSetterClass
{
    public $dependency;

    public function setUnresolvable(UnresolvableInterface $dependency)
    {
        $this->dependency = $dependency;
    }
}
