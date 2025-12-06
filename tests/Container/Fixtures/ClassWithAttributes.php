<?php

namespace System\Test\Container\Fixtures;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MyClassAttribute
{
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class MyMethodAttribute
{
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MyPropertyAttribute
{
}

#[MyClassAttribute]
class ClassWithAttributes
{
    #[MyPropertyAttribute]
    public $propertyWithAttribute;

    #[MyMethodAttribute]
    public function methodWithAttribute()
    {
    }
}
