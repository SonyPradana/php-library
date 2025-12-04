<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::flush
 */
class FlushTest extends TestCase
{
    /**
     * @test
     *
     * @testdox flush() removes all bindings
     *
     * @covers \System\Container\Container::flush */
    public function flushRemovesBindings(): void
    {
        $this->container->bind('foo', function () {
            return 'bar';
        });

        $this->assertTrue($this->container->bound('foo'));

        $this->container->flush();

        $this->assertFalse($this->container->bound('foo'));
    }

    /**
     * @test
     *
     * @testdox flush() clears singleton cache
     *
     * @covers \System\Container\Container::flush */
    public function flushClearsCache(): void
    {
        $this->container->bind('foo', fn () => new \stdClass(), true);

        $instance1 = $this->container->get('foo');
        $instance2 = $this->container->get('foo');
        $this->assertSame($instance1, $instance2);

        $this->container->flush();

        $this->container->bind('foo', fn () => new \stdClass(), true);
        $instance3 = $this->container->get('foo');

        $this->assertNotSame($instance1, $instance3);
    }

    /**
     * @test
     *
     * @testdox flush() clears alias map
     *
     * @covers \System\Container\Container::flush */
    public function flushClearsAlias(): void
    {
        $this->container->bind(\stdClass::class);
        $this->container->alias(\stdClass::class, 'foo');
        $this->assertInstanceOf(\stdClass::class, $this->container->get('foo'));

        $this->container->flush();

        $this->expectException(\System\Container\Exceptions\EntryNotFoundException::class);
        $this->container->get('foo');
    }

    /**
     * @test
     *
     * @testdox flush() produces empty clean container
     *
     * @covers \System\Container\Container::flush */
    public function flushResetsContainer(): void
    {
        // Setup the container with bindings, instances, and aliases
        $this->container->bind('foo', fn () => new \stdClass(), true);
        $this->container->get('foo'); // Resolve to create an instance
        $this->container->alias('foo', 'bar');

        $this->container->flush();

        // Assert that all relevant internal properties are now empty
        $bindings = new \ReflectionProperty($this->container, 'bindings');
        $bindings->setAccessible(true);
        $this->assertEmpty($bindings->getValue($this->container));

        $instances = new \ReflectionProperty($this->container, 'instances');
        $instances->setAccessible(true);
        $this->assertEmpty($instances->getValue($this->container));

        $aliases = new \ReflectionProperty($this->container, 'aliases');
        $aliases->setAccessible(true);
        $this->assertEmpty($aliases->getValue($this->container));

        $reflectionCache = new \ReflectionProperty($this->container, 'reflectionCache');
        $reflectionCache->setAccessible(true);
        $this->assertEmpty($reflectionCache->getValue($this->container));
    }
}
