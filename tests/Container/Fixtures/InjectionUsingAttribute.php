<?php

namespace System\Test\Container\Fixtures;

use System\Container\Attribute\Inject;

class InjectionUsingAttribute
{
    public $dependency;

    #[Inject(['dependency' => 'foo'])]
    public function setDependency(string $dependency)
    {
        $this->dependency = $dependency;
    }
}
