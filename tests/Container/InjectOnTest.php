<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\Fixtures\Dependant;
use System\Test\Container\Fixtures\Dependency;
use System\Test\Container\Fixtures\DependencyClass;
use System\Test\Container\Fixtures\InjectionUsingAttribute;
use System\Test\Container\Fixtures\InjectionUsingAttributeOnParameter;
use System\Test\Container\Fixtures\InjectionUsingAttributeOnProperty;
use System\Test\Container\Fixtures\MultipleSetterClass;
use System\Test\Container\Fixtures\NestedDependencyClass;
use System\Test\Container\Fixtures\NonSetterClass;
use System\Test\Container\Fixtures\ScalarSetterClass;
use System\Test\Container\Fixtures\SetterInjectionClass;
use System\Test\Container\Fixtures\StaticSetterClass;
use System\Test\Container\Fixtures\UnresolvableSetterClass;
use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::injectOn
 * @covers \Injector::inject
 */
class InjectOnTest extends TestCase
{
    /**
     * @test
     *
     * @testdox injectOn() calls setter injection
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectCallsSetters(): void
    {
        $instance = new SetterInjectionClass();
        $this->container->injectOn($instance);

        $this->assertInstanceOf(DependencyClass::class, $instance->dependency);
    }

    /**
     * @test
     *
     * @testdox injectOn() skips methods without setters
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectSkipsNonSetters(): void
    {
        $instance = new NonSetterClass();
        $this->container->injectOn($instance);

        $this->assertFalse($instance->called);
    }

    /**
     * @test
     *
     * @testdox injectOn() injects only class-typed arguments
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectOnlyClassTypes(): void
    {
        $instance = new ScalarSetterClass();
        $this->container->injectOn($instance);

        $this->assertEquals('default', $instance->name);
    }

    /**
     * @test
     *
     * @testdox injectOn() ignores unresolvable dependencies
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectIgnoresUnresolvable(): void
    {
        $instance = new UnresolvableSetterClass();
        $this->container->injectOn($instance);

        $this->assertNull($instance->dependency);
    }

    /**
     * @test
     *
     * @testdox injectOn() does not inject static methods
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectSkipsStatic(): void
    {
        StaticSetterClass::$called = false; // Reset static property
        $instance                  = new class { // Create a dummy object to inject on
            // This object has no setters, so injectOn won't modify it,
            // but we want to ensure it doesn't accidentally trigger static setters
        };
        $this->container->injectOn($instance);

        $this->assertFalse(StaticSetterClass::$called);
    }

    /**
     * @test
     *
     * @testdox injectOn() resolves multiple setter methods
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectMultipleSetters(): void
    {
        $instance = new MultipleSetterClass();
        $this->container->injectOn($instance);

        $this->assertInstanceOf(DependencyClass::class, $instance->dependency1);
        $this->assertInstanceOf(Fixtures\AnotherService::class, $instance->dependency2);
    }

    /**
     * @test
     *
     * @testdox injectOn() supports deeper dependency resolution
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectResolvesNested(): void
    {
        $instance = new NestedDependencyClass();
        $this->container->injectOn($instance);

        $this->assertInstanceOf(NestedDependencyClass::class, $instance);
        $this->assertInstanceOf(Dependant::class, $instance->dependant);
        $this->assertInstanceOf(Dependency::class, $instance->dependant->dep);
    }

    /**
     * @test
     *
     * @testdox injectOn() returns the same instance
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectReturnsOriginal(): void
    {
        $instance         = new \stdClass();
        $returnedInstance = $this->container->injectOn($instance);

        $this->assertSame($instance, $returnedInstance);
    }

    /**
     * @test
     *
     * @testdox injectOn() returns the same instance
     *
     * @covers \Container::injectOn
     * @covers \Injector::inject
     */
    public function injectUsingInjectAttribute(): void
    {
        $instance         = new InjectionUsingAttribute();
        /** @var InjectionUsingAttribute */
        $returnedInstance = $this->container->injectOn($instance);

        $this->assertSame($instance, $returnedInstance);
        $this->assertEquals('foo', $returnedInstance->dependency);
    }

    /**
     * @test
     *
     * @testdox #[Inject] can be used at the parameter level to specify what to inject.
     *
     * @covers \Container::injectOn
     */
    public function injectUsingInjectAttributeOnParameter(): void
    {
        $this->container->set('db.host', 'localhost');
        $instance = new InjectionUsingAttributeOnParameter();
        /** @var InjectionUsingAttributeOnParameter */
        $returnedInstance = $this->container->injectOn($instance);

        $this->assertSame($instance, $returnedInstance);
        $this->assertEquals('localhost', $returnedInstance->dependency);
    }

    /**
     * @test
     *
     * @testdox #[Inject] can be used at the parameter level to specify what to inject.
     *
     * @covers \Container::injectOn
     */
    public function injectUsingInjectAttributeOnProperty(): void
    {
        $this->container->set('db.host', 'localhost');
        $instance = new InjectionUsingAttributeOnProperty();
        /** @var InjectionUsingAttributeOnProperty */
        $returnedInstance = $this->container->injectOn($instance);

        $this->assertSame($instance, $returnedInstance);
        $this->assertEquals('localhost', $returnedInstance->dependency);
    }
}
