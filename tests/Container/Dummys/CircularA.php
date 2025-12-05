<?php

namespace System\Test\Container\Dummys;

class CircularA
{
    public function __construct(CircularB $b)
    {
    }
}
