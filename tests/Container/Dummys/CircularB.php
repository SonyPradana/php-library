<?php

namespace System\Test\Container\Dummys;

class CircularB
{
    public function __construct(CircularA $a)
    {
    }
}
