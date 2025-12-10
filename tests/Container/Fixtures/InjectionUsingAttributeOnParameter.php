<?php

namespace System\Test\Container\Fixtures;

use System\Container\Attribute\Inject;

class InjectionUsingAttributeOnParameter
{
    public $dependency;

    #[Inject]
    public function setDependency(#[Inject('db.host')] string $dependency)
    {
        $this->dependency = $dependency;
    }
}
