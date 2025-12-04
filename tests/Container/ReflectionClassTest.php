<?php

declare(strict_types=1);

namespace System\Test\Container;

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::getReflectionClass
 */
class ReflectionClassTest extends TestCase
{
    /**
     * @test
     *
     * @testdox getReflectionClass() caches ReflectionClass
     *
     * @covers \Container::getReflectionClass */
    public function reflectionCached(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() accepts string classname
     *
     * @covers \Container::getReflectionClass */
    public function reflectionString(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() throws on invalid class
     *
     * @covers \Container::getReflectionClass */
    public function reflectionInvalidClass(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() reflects public properties
     *
     * @covers \Container::getReflectionClass */
    public function reflectionProperties(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() reflects public methods
     *
     * @covers \Container::getReflectionClass */
    public function reflectionMethods(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() supports attributes
     *
     * @covers \Container::getReflectionClass */
    public function reflectionSupportsAttributes(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox getReflectionClass() distinguishes parent inheritance
     *
     * @covers \Container::getReflectionClass */
    public function reflectionInheritance(): void
    {
        $this->assertTrue(false);
    }
}
