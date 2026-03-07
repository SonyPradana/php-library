<?php

declare(strict_types=1);

namespace System\Test\Container;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use System\Container\Container;
use System\Container\Exceptions\BindingResolutionException;
use System\Container\Invoker;
use System\Test\Container\Fixtures\DependencyClass;

// Fixtures for Invoker tests
class InvokableClass
{
    public $dep;

    public function __invoke(DependencyClass $dep)
    {
        $this->dep = $dep;

        return 'invoked';
    }
}

class CallableClass
{
    public function someMethod(DependencyClass $dep)
    {
        return $dep;
    }

    public static function staticMethod(DependencyClass $dep)
    {
        return $dep;
    }
}

class CallableNoDeps
{
    public function noDepsMethod()
    {
        return 'no_deps';
    }
}

/**
 * @covers \System\Container\Invoker
 */
final class InvokerTest extends TestCase
{
    /** @var Container|MockInterface */
    private $container;
    private Invoker $invoker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = \Mockery::mock(Container::class);
        $this->invoker   = new Invoker($this->container);

        // General mocks for Resolver's needs, which Invoker also uses indirectly via container->get()
        $this->container->shouldReceive('getReflectionClass')->andReturnUsing(function ($class) {
            return new \ReflectionClass($class);
        });
        $this->container->shouldReceive('getConstructorParameters')->andReturnUsing(function ($class) {
            $reflection = new \ReflectionClass($class);

            return $reflection->getConstructor() ? $reflection->getConstructor()->getParameters() : null;
        });
        $this->container->shouldReceive('getLastParameterOverride')->andReturn([]);
        $this->container->shouldReceive('bound')->andReturn(false); // Assume nothing is bound by default
        $this->container->shouldReceive('make')->andReturnUsing(function ($abstract, $parameters = []) {
            // Invoker doesn't use Resolver, but get() and make() from Container would.
            // For now, let's assume get() is used for concrete dependency resolution
            return new $abstract();
        });
        $dependencyInstance = new DependencyClass();
        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturn($dependencyInstance)->byDefault();
        // General mock for other 'get' calls if needed, though most Invoker tests are specific.
        $this->container->shouldReceive('get')->andReturnUsing(function ($abstract) {
            return new $abstract();
        })->byDefault();
        $this->container->shouldReceive('getReflectionMethod')->andReturnUsing(function ($class, $method) {
            return new \ReflectionMethod($class, $method);
        });
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function itCanInvokeAClosureWithDependencies(): void
    {
        $dep = new DependencyClass();
        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturn($dep);

        $result = $this->invoker->call(function (DependencyClass $d) {
            return $d;
        });

        $this->assertSame($dep, $result);
    }

    /** @test */
    public function itCanInvokeAClassMethodWithDependencies(): void
    {
        $dep = new DependencyClass();
        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturn($dep);
        $this->container->shouldReceive('get')->with(CallableClass::class)->andReturn(new CallableClass());

        $result = $this->invoker->call([CallableClass::class, 'someMethod']);

        $this->assertSame($dep, $result);
    }

    /** @test */
    public function itCanInvokeAStaticClassMethodWithDependencies(): void
    {
        $dep = new DependencyClass();
        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturn($dep);

        $result = $this->invoker->call([CallableClass::class, 'staticMethod']);

        $this->assertSame($dep, $result);
    }

    /** @test */
    public function itCanInvokeAnInvokableClass(): void
    {
        $dep = new DependencyClass();
        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturn($dep);
        $this->container->shouldReceive('get')->with(InvokableClass::class)->andReturn(new InvokableClass());

        $invokableInstance = $this->invoker->call(InvokableClass::class);

        $this->assertEquals('invoked', $invokableInstance);
        $this->assertSame($dep, $this->container->get(InvokableClass::class)->dep);
    }

    /** @test */
    public function itOverridesParametersCorrectly(): void
    {
        $mockedDep   = new DependencyClass();
        $overrideDep = new DependencyClass(); // Different instance

        $this->container->shouldReceive('get')->with(DependencyClass::class)->andReturn($mockedDep);

        $result = $this->invoker->call(function (DependencyClass $d) {
            return $d;
        }, ['d' => $overrideDep]);

        $this->assertSame($overrideDep, $result);
    }

    /** @test */
    public function itThrowsExceptionForUnsupportedCallableType(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('System\Container\Invoker::call(): Argument #1 ($callable) must be of type callable|object|array|string, int given');

        $this->invoker->call(123); // Invalid callable type
    }

    /** @test */
    public function itThrowsExceptionIfInvokableClassHasNoInvokeMethod(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessageMatches('/^Class System\\\\Test\\\\Container\\\\CallableNoDeps does not have an __invoke\(\) method\. Cannot be used as invokable\.$/');

        $this->invoker->call(CallableNoDeps::class);
    }
}
