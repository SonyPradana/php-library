<?php

declare(strict_types=1);

namespace System\Test\Container\Fixtures;

class ClassWithUnionTypeConstructor
{
    public $dependency;

    public function __construct(UnionDependencyOne|UnionDependencyTwo $dependency)
    {
        $this->dependency = $dependency;
    }
}
