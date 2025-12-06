<?php

namespace System\Test\Container\Fixtures;

class NestedDependencyClass
{
    public $dependant;

    public function setDependant(Dependant $dependant)
    {
        $this->dependant = $dependant;
    }
}
