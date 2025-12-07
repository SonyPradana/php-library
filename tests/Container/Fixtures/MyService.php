<?php

declare(strict_types=1);

namespace System\Test\Container\Fixtures;

class MyService
{
    public function myMethod(Service $service)
    {
        return $service;
    }
}
