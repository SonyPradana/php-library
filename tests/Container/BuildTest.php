<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\Fixtures\CircularA;
use System\Test\Container\Fixtures\ClassWithMissingDependency;
use System\Test\Container\Fixtures\ClassWithNullableUnionTypeConstructor;
use System\Test\Container\Fixtures\ClassWithUnionTypeConstructor;
use System\Test\Container\Fixtures\Dependant;
use System\Test\Container\Fixtures\Dependency;
use System\Test\Container\Fixtures\DependencyClass;
use System\Test\Container\Fixtures\PrivateConstructorClass;
use System\Test\Container\Fixtures\ScalarConstructorClass;
use System\Test\Container\Fixtures\Service;
use System\Test\Container\Fixtures\TypedConstructorClass;
use System\Test\Container\Fixtures\UnionDependencyOne;
use System\Test\Container\Fixtures\UnionDependencyTwo;
use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::build
 */
class BuildTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->container->flush();
    }

    /**
     * @test
     *
     * @testdox Build can construct class via reflection
     *
     * @covers \Container::build
     */
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
     * @covers \Container::build
     */
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
     * @covers \Container::build
     */
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
     * @covers \Container::build
     */
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
     * @covers \Container::build
     */
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
     * @covers \Container::build
     */
    public function buildCircularDependency(): void
    {
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);

        $container = $this->container;
        $container->build(CircularA::class);
    }

    /**
     * @test
     *
     * @testdox Build resolves typed constructor params
     *
     * @covers \Container::build
     */
    public function buildTypedConstructor(): void
    {
        $instance = $this->container->build(TypedConstructorClass::class);

        $this->assertInstanceOf(TypedConstructorClass::class, $instance);
        $this->assertInstanceOf(DependencyClass::class, $instance->dep);
    }

    /**
     * @test
     *
     * @testdox build resolves the first dependency in a union type
     */
    public function buildResolvesFirstUnionType(): void
    {
        $this->container->bind(UnionDependencyOne::class, fn () => new UnionDependencyOne());
        $instance = $this->container->build(ClassWithUnionTypeConstructor::class);
        $this->assertInstanceOf(UnionDependencyOne::class, $instance->dependency);
    }

    /**
     * @test
     *
     * @testdox build resolves the second dependency in a union type
     */
    public function buildResolvesSecondUnionType(): void
    {
        $this->container->bind(UnionDependencyTwo::class, fn () => new UnionDependencyTwo());
        $instance = $this->container->build(ClassWithUnionTypeConstructor::class);
        $this->assertInstanceOf(UnionDependencyTwo::class, $instance->dependency);
    }

    /**
     * @test
     *
     * @testdox build throws when no union type dependency is bound
     */
    public function buildThrowsWhenNoUnionTypeIsBound(): void
    {
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);
        $this->container->build(ClassWithUnionTypeConstructor::class);
    }

    /**
     * @test
     *
     * @testdox build resolves nullable union types to null
     */
    public function buildNullableUnionTypeConstructor(): void
    {
        // Resolve to null when no type is bound and the parameter is nullable
        $instance = $this->container->build(ClassWithNullableUnionTypeConstructor::class);
        $this->assertNull($instance->dependency);
    }

    /**
     * @test
     *
     * @testdox build() with scalar param throws exception
     *
     * @covers \Container::build
     */
    public function buildWithScalarParamThrows(): void
    {
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);

        $this->container->build(ScalarConstructorClass::class);
    }

    /**
     * @test
     *
     * @testdox private constructor → BindingResolutionException
     *
     * @covers \Container::build
     */
    public function buildPrivateConstructorThrowsException(): void
    {
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);

        $this->container->build(PrivateConstructorClass::class);
    }
}
