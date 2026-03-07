<?php

namespace System\Test\Container\Fixtures;

class CircularB
{
    public function __construct(CircularA $a)
    {
    }
}
