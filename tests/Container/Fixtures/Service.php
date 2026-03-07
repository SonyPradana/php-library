<?php

namespace System\Test\Container\Fixtures;

class Service
{
    public function __construct(public $value = 'default')
    {
    }
}
