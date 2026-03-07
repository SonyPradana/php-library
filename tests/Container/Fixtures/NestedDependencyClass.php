<?php

namespace System\Test\Container\Fixtures;

use System\Container\Attribute\Inject;

class NestedDependencyClass
{
    public $dependant;

    #[Inject]
    public function setDependant(Dependant $dependant)
    {
        $this->dependant = $dependant;
    }
}
