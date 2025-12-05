<?php

namespace System\Test\Container\Dummys;

class DeepA
{
    public function __construct(public DeepB $b)
    {
    }
}
