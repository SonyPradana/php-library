<?php

namespace System\Test\Container\Fixtures;

class CircularA
{
    public function __construct(CircularB $b)
    {
    }
}
