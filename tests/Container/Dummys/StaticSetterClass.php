<?php

namespace System\Test\Container\Dummys;

class StaticSetterClass
{
    public static $called = false;

    public static function setStaticDependency(DependencyClass $dependency)
    {
        self::$called = true;
    }
}
