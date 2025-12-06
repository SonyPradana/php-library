<?php

namespace System\Test\Container\Fixtures;

class DeepA
{
    public function __construct(public DeepB $b)
    {
    }
}
