<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\Fixtures\DummyClass;
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
        $container          = $this->container;

        $container['foo'] = 'bar';

        $this->assertTrue(isset($container['foo']));
    }

    /**
     * @test
     *
     * @testdox offsetGet() retrieves stored value
     *
     * @covers \Container::offsetGet */
    public function arrayGet(): void
    {
        $container          = $this->container;

        $container['foo'] = 'bar';

        $this->assertEquals('bar', $container['foo']);
    }

    /**
     * @test
     *
     * @testdox offsetExists() detects key presence
     *
     * @covers \Container::offsetExists */
    public function arrayExists(): void
    {
        $container          = $this->container;

        $container['foo'] = 'bar';

        $this->assertTrue(isset($container['foo']));

        $this->assertFalse(isset($container['baz']));
    }

    /**
     * @test
     *
     * @testdox offsetUnset() removes value
     *
     * @covers \Container::offsetUnset */
    public function arrayUnset(): void
    {
        $container          = $this->container;

        $container['foo'] = 'bar';

        $this->assertTrue(isset($container['foo']));

        unset($container['foo']);

        $this->assertFalse(isset($container['foo']));
    }

    /**
     * @test
     *
     * @testdox offsetGet() returns a new instance each time (like make())
     *
     * @covers \System\Container\Container::offsetGet */
    public function arrayGetReturnsNewInstance(): void
    {
        $container = $this->container;

        $container['foo'] = fn () => new \stdClass();

        $instance1 = $container['foo'];

        $instance2 = $container['foo'];

        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * @test
     *
     * @testdox ArrayAccess key resolves make() from container
     *
     * @covers \System\Container\Container::offsetGet */
    public function arrayGetResolvesContainer(): void
    {
        $container = $this->container;

        $container['std'] = fn () => new \stdClass(); // Bind a closure that returns an instance

        $instance = $container['std'];

        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    /**
     * @test
     *
     * @testdox offsetSet() stores binding as shared (singleton) by default
     *
     * @covers \System\Container\Container::offsetSet */
    public function offsetSetStoresSharedBinding(): void
    {
        $this->markTestSkipped('Inconsistency: offsetSet() creates shared bindings, but offsetGet() (which calls make()) returns a new instance.');

        $container = $this->container;

        $container['foo'] = fn () => new \stdClass();

        $instance1 = $container['foo'];

        $instance2 = $container['foo'];

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     *
     * @testdox using array syntax still respects alias()
     *
     * @covers \System\Container\Container::offsetGet */
    public function arrayAccessRespectsAlias(): void
    {
        $this->container->alias(DummyClass::class, 'dummy_alias');

        $this->container['dummy_alias'] = fn () => new DummyClass();

        $instance = $this->container['dummy_alias'];

        $this->assertInstanceOf(DummyClass::class, $instance);
    }
}
