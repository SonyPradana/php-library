<?php

declare(strict_types=1);

namespace System\Test\Container\Dummys;

class DummyStaticClass
{
    public static function staticMethod(): string
    {
        return 'static called';
    }
}
