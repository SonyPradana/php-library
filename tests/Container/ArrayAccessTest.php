<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::offsetGet
 * @covers \Container::offsetExists
 * @covers \Container::offsetSet
 * @covers \Container::offsetUnset
 */
class ArrayAccessTest extends TestCase
{
    /**
     * @test
     *
     * @testdox offsetSet() stores value
     *
     * @covers \Container::offsetSet */
    public function arraySet(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox offsetGet() retrieves stored value
     *
     * @covers \Container::offsetGet */
    public function arrayGet(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox offsetExists() detects key presence
     *
     * @covers \Container::offsetExists */
    public function arrayExists(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox offsetUnset() removes value
     *
     * @covers \Container::offsetUnset */
    public function arrayUnset(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox ArrayAccess integrates with container binding
     *
     * @covers \Container::offsetSet */
    public function arrayBind(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox ArrayAccess key resolves get() from container
     *
     * @covers \Container::offsetGet */
    public function arrayGetResolvesContainer(): void
    {
        $this->assertTrue(false);
    }
}
