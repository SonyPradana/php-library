<?php

namespace System\Test\Container\Dummys;

class Service
{
    public function __construct(public $value = 'default')
    {
    }
}
