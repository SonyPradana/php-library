<?php

namespace System\Test\Container\Fixtures;

class StaticSetterClass
{
    public static $called = false;

    public static function setStaticDependency(DependencyClass $dependency)
    {
        self::$called = true;
    }
}
