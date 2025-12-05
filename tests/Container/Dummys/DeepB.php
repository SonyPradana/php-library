<?php

namespace System\Test\Container\Dummys;

class DeepB
{
    public function __construct(public DeepC $c)
    {
    }
}
