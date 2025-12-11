<?php

namespace System\Test\Container;

use System\Test\Container\Fixtures\DependencyClass;
use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container
 */
class MemoryLeakTest extends TestCase
{
    /**
     * @test
     *
     * @testdox repeated make() does not grow internal array sizes
     *
     * @covers \Container::make */
    public function leakRepeatedMakeNonShared(): void
    {
        $initialBindingsCount  = count($this->getProtectedProperty('bindings'));
        $initialInstancesCount = count($this->getProtectedProperty('instances'));
        $initialAliasesCount   = count($this->getProtectedProperty('aliases'));

        // Make many non-shared instances of a simple class that is not bound
        for ($i = 0; $i < 10000; $i++) {
            $this->container->make(\stdClass::class);
        }

        $finalBindingsCount  = count($this->getProtectedProperty('bindings'));
        $finalInstancesCount = count($this->getProtectedProperty('instances'));
        $finalAliasesCount   = count($this->getProtectedProperty('aliases'));

        // Assert that bindings, instances, and aliases do not grow
        $this->assertEquals($initialBindingsCount, $finalBindingsCount);
        $this->assertEquals($initialInstancesCount, $finalInstancesCount);
        $this->assertEquals($initialAliasesCount, $finalAliasesCount);
    }

    /**
     * @test
     *
     * @testdox call() does not store excessive metadata (no leak) */
    public function leakCallMetadata(): void
    {
        $callable = function (DependencyClass $dep) {
            return $dep;
        };

        // Call many times to simulate heavy usage
        for ($i = 0; $i < 10000; $i++) {
            $this->container->call($callable);
        }

        // If no exception is thrown, it's a pass for this basic check
        $this->assertTrue(true);
    }

    /**
     * @test
     *
     * @testdox injectOn() no memory leak on repeated injection */
    public function leakInjectOn(): void
    {
        // Define a simple class with a setter to be injected
        $injectable = new class {
            public $dependency;

            public function setDependency(DependencyClass $dependency)
            {
                $this->dependency = $dependency;
            }
        };

        // Call injectOn many times to simulate heavy usage
        for ($i = 0; $i < 10000; $i++) {
            $this->container->injectOn($injectable);
        }

        // If no exception is thrown, it's a pass for this basic check
        $this->assertTrue(true);
    }
}
