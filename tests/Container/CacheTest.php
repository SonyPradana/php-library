<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::enableCache
 * @covers \Container::clearCache
 */
class CacheTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Cache can be enabled
     *
     * @covers \System\Container\Container::enableCache */
    public function cacheEnable(): void
    {
        $this->container->enableCache(false);
        $this->container->build(\stdClass::class);

        $reflectionCache = new \ReflectionProperty($this->container, 'reflectionCache');
        $reflectionCache->setAccessible(true);
        $this->assertArrayNotHasKey(\stdClass::class, $reflectionCache->getValue($this->container));

        $constructorCache = new \ReflectionProperty($this->container, 'constructorCache');
        $constructorCache->setAccessible(true);
        $this->assertArrayNotHasKey(\stdClass::class, $constructorCache->getValue($this->container));

        $this->container->enableCache(true);
        $this->container->build(\stdClass::class);

        $this->assertArrayHasKey(\stdClass::class, $reflectionCache->getValue($this->container));
        $this->assertArrayHasKey(\stdClass::class, $constructorCache->getValue($this->container));
    }

    /**
     * @test
     *
     * @testdox Cache disabled yields no caching
     *
     * @covers \System\Container\Container::enableCache */
    public function cacheDisable(): void
    {
        $this->container->enableCache(true);
        $this->container->build(\stdClass::class);

        $reflectionCache = new \ReflectionProperty($this->container, 'reflectionCache');
        $reflectionCache->setAccessible(true);
        $this->assertArrayHasKey(\stdClass::class, $reflectionCache->getValue($this->container));

        $constructorCache = new \ReflectionProperty($this->container, 'constructorCache');
        $constructorCache->setAccessible(true);
        $this->assertArrayHasKey(\stdClass::class, $constructorCache->getValue($this->container));

        $this->container->enableCache(false);
        $this->container->build(DummyClass::class); // Use DummyClass instead

        $this->assertArrayNotHasKey(DummyClass::class, $reflectionCache->getValue($this->container));
        $this->assertArrayNotHasKey(DummyClass::class, $constructorCache->getValue($this->container));
    }

    /**
     * @test
     *
     * @testdox Cache stores resolved entries
     *
     * @covers \System\Container\Container::enableCache */
    public function cacheStoresValues(): void
    {
        $this->container->enableCache(true);
        $this->container->build(\stdClass::class);

        $reflectionCache = new \ReflectionProperty($this->container, 'reflectionCache');
        $reflectionCache->setAccessible(true);
        $this->assertArrayHasKey(\stdClass::class, $reflectionCache->getValue($this->container));

        $constructorCache = new \ReflectionProperty($this->container, 'constructorCache');
        $constructorCache->setAccessible(true);
        $this->assertArrayHasKey(\stdClass::class, $constructorCache->getValue($this->container));
    }

    /**
     * @test
     *
     * @testdox clearCache() empties resolution cache
     *
     * @covers \System\Container\Container::clearCache */
    public function cacheClear(): void
    {
        $this->container->enableCache(true);
        $this->container->build(\stdClass::class);

        $reflectionCache = new \ReflectionProperty($this->container, 'reflectionCache');
        $reflectionCache->setAccessible(true);
        $this->assertArrayHasKey(\stdClass::class, $reflectionCache->getValue($this->container));

        $constructorCache = new \ReflectionProperty($this->container, 'constructorCache');
        $constructorCache->setAccessible(true);
        $this->assertArrayHasKey(\stdClass::class, $constructorCache->getValue($this->container));

        $this->container->clearCache();

        $this->assertArrayNotHasKey(\stdClass::class, $reflectionCache->getValue($this->container));
        $this->assertArrayNotHasKey(\stdClass::class, $constructorCache->getValue($this->container));
    }

    /**
     * @test
     *
     * @testdox Cache isolates between get() and make()
     *
     * @covers \System\Container\Container::enableCache */
    public function cacheIsolatedBetweenGetAndMake(): void
    {
        $this->container->enableCache(true);
        $this->container->bind(DummyClass::class, null, true); // Bind as shared (singleton)

        // First get() call should create and cache the instance
        $instance1 = $this->container->get(DummyClass::class);
        $this->assertInstanceOf(DummyClass::class, $instance1);

        // Second get() call should return the same cached instance
        $instance2 = $this->container->get(DummyClass::class);
        $this->assertSame($instance1, $instance2);

        // First make() call should return a new instance
        $instance3 = $this->container->make(DummyClass::class);
        $this->assertInstanceOf(DummyClass::class, $instance3);
        $this->assertNotSame($instance1, $instance3);

        // Second make() call should return another new instance
        $instance4 = $this->container->make(DummyClass::class);
        $this->assertInstanceOf(DummyClass::class, $instance4);
        $this->assertNotSame($instance1, $instance4);
        $this->assertNotSame($instance3, $instance4);
    }

    /**
     * @test
     *
     * @testdox Cache does not break alias resolution
     *
     * @covers \System\Container\Container::enableCache */
    public function cacheAliasSafe(): void
    {
        $this->container->enableCache(true);
        $this->container->alias(DummyClass::class, 'dummy_alias');

        $instance = $this->container->make('dummy_alias');
        $this->assertInstanceOf(DummyClass::class, $instance);

        $reflectionCache = new \ReflectionProperty($this->container, 'reflectionCache');
        $reflectionCache->setAccessible(true);
        $this->assertArrayHasKey(DummyClass::class, $reflectionCache->getValue($this->container));
        $this->assertArrayNotHasKey('dummy_alias', $reflectionCache->getValue($this->container));

        $constructorCache = new \ReflectionProperty($this->container, 'constructorCache');
        $constructorCache->setAccessible(true);
        $this->assertArrayHasKey(DummyClass::class, $constructorCache->getValue($this->container));
        $this->assertArrayNotHasKey('dummy_alias', $constructorCache->getValue($this->container));
    }
}

class DummyClass
{
}
