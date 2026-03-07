<?php

declare(strict_types=1);

namespace System\Test\Container\Fixtures;

class ClassWithNullableUnionTypeConstructor
{
    public $dependency;

    public function __construct(UnionDependencyOne|UnionDependencyTwo|null $dependency = null)
    {
        $this->dependency = $dependency;
    }
}
