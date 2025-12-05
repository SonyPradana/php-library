<?php

namespace System\Test\Container\Dummys;

class NestedDependencyClass
{
    public $dependant;

    public function setDependant(Dependant $dependant)
    {
        $this->dependant = $dependant;
    }
}
