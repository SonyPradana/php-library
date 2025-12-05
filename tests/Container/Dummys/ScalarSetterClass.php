<?php

namespace System\Test\Container\Dummys;

class ScalarSetterClass
{
    public $name = 'default';

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
