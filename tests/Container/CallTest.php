<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\Fixtures\DependencyClass;
use System\Test\Container\Fixtures\DummyStaticClass;
use System\Test\Container\Fixtures\InvokableClass;
use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::call*/
class CallTest extends TestCase
{
    /**
     * @test
     *
     * @testdox call() invokes a function
     *
     * @covers \System\Container\Container::call
     */
    public function callFunction(): void
    {
        $result = $this->container->call(function () {
            return 'called';
        });
        $this->assertEquals('called', $result);
    }

    /**
     * @test
     *
     * @testdox call() invokes method array syntax
     *
     * @covers \System\Container\Container::call
     */
    public function callClassMethod(): void
    {
        $dummy = new class {
            public function foo()
            {
                return 'bar';
            }
        };

        $result = $this->container->call([$dummy, 'foo']);
        $this->assertEquals('bar', $result);
    }

    /**
     * @test
     *
     * @testdox call() invokes static method string syntax
     *
     * @covers \System\Container\Container::call
     */
    public function callStaticMethod(): void
    {
        $result = $this->container->call([DummyStaticClass::class, 'staticMethod']);
        $this->assertEquals('static called', $result);
    }

    /**
     * @test
     *
     * @testdox call() injects dependencies in parameters
     *
     * @covers \System\Container\Container::call
     */
    public function callInjectsDependencies(): void
    {
        $result = $this->container->call(function (DependencyClass $dependency) {
            return $dependency;
        });
        $this->assertInstanceOf(DependencyClass::class, $result);
    }

    /**
     * @test
     *
     * @testdox call() merges user parameters and auto injection
     *
     * @covers \System\Container\Container::call
     */
    public function callWithCustomParameters(): void
    {
        $result = $this->container->call(function (DependencyClass $dependency, string $name) {
            return [$dependency, $name];
        }, ['name' => 'test']);
        $this->assertInstanceOf(DependencyClass::class, $result[0]);
        $this->assertEquals('test', $result[1]);
    }

    /**
     * @test
     *
     * @testdox call() resolves callable from container binding
     *
     * @covers \System\Container\Container::call
     */
    public function callResolvesViaContainer(): void
    {
        $this->container->bind(DependencyClass::class, function () {
            return new DependencyClass();
        });

        $result = $this->container->call(function (DependencyClass $dependency) {
            return $dependency;
        });
        $this->assertInstanceOf(DependencyClass::class, $result);
    }

    /**
     * @test
     *
     * @testdox call() throws on unresolvable param
     *
     * @covers \System\Container\Container::call
     */
    public function callUnresolvableParameter(): void
    {
        $this->expectException(\System\Container\Exceptions\BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to resolve dependency [Parameter #0 [ <required> $param ]] in callable');

        $this->container->call(function ($param) {
        });
    }

    /**
     * @test
     *
     * @testdox call() invokes invokable class
     *
     * @covers \System\Container\Container::call
     */
    public function callInvokableClass(): void
    {
        $result = $this->container->call(InvokableClass::class);

        $this->assertEquals('invoked', $result);
    }
}
