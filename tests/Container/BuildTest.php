<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;
use System\Test\Container\DependencyClass;
use System\Test\Container\AnotherService; // Added

class Dependency
{
}
class Dependant
{
    public function __construct(public Dependency $dep)
    {
    }
}

class CircularA
{
    public function __construct(CircularB $b)
    {
    }
}
class CircularB
{
    public function __construct(CircularA $a)
    {
    }
}

interface UnresolvableInterface
{
}
class ClassWithMissingDependency
{
    public function __construct(UnresolvableInterface $dep)
    {
    }
}

class Service
{
    public function __construct(public $value = 'default')
    {
    }
}

class TypedConstructorClass
{
    public function __construct(public DependencyClass $dep)
    {
    }
}

class UnionTypeConstructorClass
{
    public function __construct(public DependencyClass|AnotherService $dep)
    {
    }
}

/**
 * @covers \Container::build
 */
class BuildTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Build can construct class via reflection
     *
     * @covers \Container::build */
    public function buildConstructsClass(): void
    {
        $container = $this->container;
        $instance  = $container->build(\stdClass::class);

        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    /**
     * @test
     *
     * @testdox Build resolves constructor dependencies
     *
     * @covers \Container::build */
    public function buildWithDependencies(): void
    {
        $container = $this->container;
        $instance  = $container->build(Dependant::class);

        $this->assertInstanceOf(Dependant::class, $instance);
        $this->assertInstanceOf(Dependency::class, $instance->dep);
    }

    /**
     * @test
     *
     * @testdox Build with parameters overrides constructor defaults
     *
     * @covers \Container::build */
    public function buildWithCustomParameters(): void
    {
        $container = $this->container;
        $instance  = $container->build(Service::class, ['value' => 'custom']);

        $this->assertEquals('custom', $instance->value);
    }

    /**
     * @test
     *
     * @testdox Build from closure returns result
     *
     * @covers \Container::build */
    public function buildFromClosure(): void
    {
        $container = $this->container;
        $result    = $container->build(fn () => 'foo');

        $this->assertEquals('foo', $result);
    }

    /**
     * @test
     *
     * @testdox Build fails on missing dependency
     *
     * @covers \Container::build */
    public function buildMissingDependency(): void
    {
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);

        $container = $this->container;
        $container->build(ClassWithMissingDependency::class);
    }

    /**
     * @test
     *
     * @testdox Build handles circular dependency error
     *
     * @covers \Container::build */
    public function buildCircularDependency(): void
    {
        $this->markTestSkipped('Circular dedependency make memory limit exceed.');
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);

        $container = $this->container;
        $container->build(CircularA::class);
    }

    /**
     * @test
     *
     * @testdox Build resolves typed constructor params
     *
     * @covers \Container::build */
    public function buildTypedConstructor(): void
    {
        $instance = $this->container->build(TypedConstructorClass::class);

        $this->assertInstanceOf(TypedConstructorClass::class, $instance);
        $this->assertInstanceOf(DependencyClass::class, $instance->dep);
    }

    /**
     * @test
     *
     * @testdox Build resolves union types or throws
     *
     * @covers \Container::build */
    public function buildUnionTypeConstructor(): void
    {
        $this->markTestSkipped('Current Container implementation does not support resolving union types in constructor parameters.');
        $this->assertTrue(false);
    }
}