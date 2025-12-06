<?php

namespace System\Test\Container\Fixtures;

class DeepB
{
    public function __construct(public DeepC $c)
    {
    }
}
