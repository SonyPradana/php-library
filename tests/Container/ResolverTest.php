<?php

declare(strict_types=1);

namespace System\Test\Container;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use System\Container\Container;
use System\Container\Exceptions\BindingResolutionException;
use System\Container\Resolver;
use System\Test\Container\Fixtures\CircularA;
use System\Test\Container\Fixtures\DependencyClass;
use System\Test\Container\Fixtures\TypedConstructorClass;

/**
 * @covers \System\Container\Resolver
 */
final class ResolverTest extends TestCase
{
    /** @var Container|MockInterface */
    private $container;
    private Resolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = \Mockery::mock(Container::class);
        $this->container->shouldReceive('getLastParameterOverride')->andReturn([]);
        $this->container->shouldReceive('make')->andReturnUsing(function ($abstract, $parameters = []) {
            try {
                // Simulate Container's make() calling Resolver's resolveClass()
                // This is needed for nested resolutions within resolveParameterDependency
                return $this->resolver->resolveClass($abstract, $parameters);
            } catch (BindingResolutionException $e) {
                // Re-throw the exception caught from resolver. This allows circular detection to propagate.
                throw $e;
            }
        });

        // General expectations for reflection calls needed by the Resolver during nested resolutions
        $this->container->shouldReceive('getReflectionClass')->andReturnUsing(function ($class) {
            return new \ReflectionClass($class);
        });
        $this->container->shouldReceive('getConstructorParameters')->andReturnUsing(function ($class) {
            $reflection = new \ReflectionClass($class);

            return $reflection->getConstructor() ? $reflection->getConstructor()->getParameters() : null;
        });
        // General expectation for bound()
        $this->container->shouldReceive('bound')->andReturn(false);

        $this->resolver  = new Resolver($this->container);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function itCanResolveAClassWithoutAConstructor(): void
    {
        $this->container->shouldReceive('getReflectionClass')->with(\stdClass::class)
            ->andReturn(new \ReflectionClass(\stdClass::class));
        $this->container->shouldReceive('getConstructorParameters')->with(\stdClass::class)
            ->andReturn(null);

        $instance = $this->resolver->resolveClass(\stdClass::class);

        $this->assertInstanceOf(\stdClass::class, $instance);
    }

    /** @test */
    public function itCanResolveAClassWithDependencies(): void
    {
        // Mocking for TypedConstructorClass
        $reflectionTyped = new \ReflectionClass(TypedConstructorClass::class);
        $this->container->shouldReceive('getReflectionClass')->with(TypedConstructorClass::class)->andReturn($reflectionTyped);
        $this->container->shouldReceive('getConstructorParameters')->with(TypedConstructorClass::class)->andReturn($reflectionTyped->getConstructor()->getParameters());

        // Mocking for DependencyClass resolution
        $this->container->shouldReceive('bound')->with(DependencyClass::class)->andReturn(false);
        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturnUsing(function () {
            $reflectionDep = new \ReflectionClass(DependencyClass::class);
            $this->container->shouldReceive('getReflectionClass')->with(DependencyClass::class)->andReturn($reflectionDep);
            $this->container->shouldReceive('getConstructorParameters')->with(DependencyClass::class)->andReturn(null);

            return $this->resolver->resolveClass(DependencyClass::class);
        });

        $instance = $this->resolver->resolveClass(TypedConstructorClass::class);

        $this->assertInstanceOf(TypedConstructorClass::class, $instance);
        $this->assertInstanceOf(DependencyClass::class, $instance->dep);
    }

    /** @test */
    public function itThrowsExceptionOnCircularDependency(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Circular dependency detected');

        // Mocking for CircularA
        $reflectionA = new \ReflectionClass(CircularA::class);
        $this->container->shouldReceive('getReflectionClass')->with(CircularA::class)->andReturn($reflectionA);
        $this->container->shouldReceive('getConstructorParameters')->with(CircularA::class)->andReturn($reflectionA->getConstructor()->getParameters());

        $this->resolver->resolveClass(CircularA::class);
    }
}
