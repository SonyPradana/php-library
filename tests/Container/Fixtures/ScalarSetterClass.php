<?php

namespace System\Test\Container\Fixtures;

class ScalarSetterClass
{
    public $name = 'default';

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
