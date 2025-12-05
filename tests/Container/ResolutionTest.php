<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Container\Exceptions\BindingResolutionException;
use System\Container\Exceptions\EntryNotFoundException;
use System\Test\Container\Dummys\DeepA;
use System\Test\Container\Dummys\DeepB;
use System\Test\Container\Dummys\DeepC;
use System\Test\Container\Dummys\DependencyClass;
use System\Test\Container\Dummys\Service;
use System\Test\Container\Dummys\UnresolvableClass;
use System\Test\Container\TestContainer as TestCase;

/**
 *  @covers \Container::get
 *  @covers \Container::make
 */
class ResolutionTest extends TestCase
{
    /**
     * @test
     *
     * @testdox get() resolves shared instance
     *
     *  @covers \Container::get */
    public function getResolvesShared(): void
    {
        $this->container->bind(DependencyClass::class, null, true);

        $instance1 = $this->container->get(DependencyClass::class);
        $instance2 = $this->container->get(DependencyClass::class);

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     *
     * @testdox get() throws when entry not found
     *
     *  @covers \Container::get */
    public function getNotFound(): void
    {
        $this->expectException(EntryNotFoundException::class);

        $this->container->get('non-existent-class');
    }

    /**
     * @test
     *
     * @testdox make() always returns new instance
     *
     *  @covers \Container::make */
    public function makeFreshInstance(): void
    {
        $instance1 = $this->container->make(\stdClass::class);
        $instance2 = $this->container->make(\stdClass::class);

        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * @test
     *
     * @testdox make() accepts parameters
     *
     *  @covers \Container::make */
    public function makeWithParameters(): void
    {
        $instance = $this->container->make(Service::class, ['value' => 'custom']);

        $this->assertInstanceOf(Service::class, $instance);
        $this->assertEquals('custom', $instance->value);
    }

    /**
     * @test
     *
     * @testdox get() resolves via closure binding
     *
     *  @covers \Container::get */
    public function getClosure(): void
    {
        $this->container->bind('test-closure', function ($container) {
            return 'resolved from closure';
        });

        $result = $this->container->get('test-closure');
        $this->assertEquals('resolved from closure', $result);
    }

    /**
     * @test
     *
     * @testdox make() resolves via closure
     *
     *  @covers \Container::make */
    public function makeClosure(): void
    {
        $this->container->bind('test-closure', function ($container) {
            return 'resolved from closure';
        });

        $result = $this->container->make('test-closure');
        $this->assertEquals('resolved from closure', $result);
    }

    /**
     * @test
     *
     * @testdox get() respects alias resolution
     *
     *  @covers \Container::get */
    public function getViaAlias(): void
    {
        $this->container->bind('dependency', DependencyClass::class);
        $this->container->alias('dependency', 'alias');

        $this->assertInstanceOf(
            DependencyClass::class,
            $this->container->get('alias')
        );
    }

    /**
     * @test
     *
     * @testdox make() respects alias resolution
     *
     *  @covers \Container::make */
    public function makeViaAlias(): void
    {
        $this->container->bind(DependencyClass::class, null, false); // Make sure it's non-shared
        $this->container->alias(DependencyClass::class, 'dependency_alias');

        $instance = $this->container->make('dependency_alias');
        $this->assertInstanceOf(DependencyClass::class, $instance);
    }

    /**
     * @test
     *
     * @testdox get() caches resolved singleton
     *
     *  @covers \Container::get */
    public function getSingletonCached(): void
    {
        $counter = 0;

        $this->container->bind(DependencyClass::class, function () use (&$counter) {
            $counter++;

            return new DependencyClass();
        }, true);

        $this->container->get(DependencyClass::class);
        $this->container->get(DependencyClass::class);

        $this->assertEquals(1, $counter);
    }

    /**
     * @test
     *
     * @testdox get() resolves deep dependencies
     *
     *  @covers \Container::get */
    public function getResolvesRecursiveDependencies(): void
    {
        $instance = $this->container->get(DeepA::class);

        $this->assertInstanceOf(DeepA::class, $instance);
        $this->assertInstanceOf(DeepB::class, $instance->b);
        $this->assertInstanceOf(DeepC::class, $instance->b->c);
    }

    /**
     * @test
     *
     * @testdox make() resolves deep dependencies
     *
     *  @covers \Container::make */
    public function makeResolvesRecursiveDependencies(): void
    {
        $instance = $this->container->make(DeepA::class);

        $this->assertInstanceOf(DeepA::class, $instance);
        $this->assertInstanceOf(DeepB::class, $instance->b);
        $this->assertInstanceOf(DeepC::class, $instance->b->c);
    }

    /**
     * @test
     *
     * @testdox make() throws on unresolvable dependency
     *
     *  @covers \Container::make */
    public function makeUnresolvableDependency(): void
    {
        $this->expectException(BindingResolutionException::class);

        $this->container->make(UnresolvableClass::class);
    }
}
