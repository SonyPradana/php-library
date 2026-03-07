<?php

namespace System\Test\Container\Fixtures;

use System\Container\Attribute\Inject;

class InjectionUsingAttributeOnProperty
{
    #[Inject('db.host')]
    public $dependency;
}
