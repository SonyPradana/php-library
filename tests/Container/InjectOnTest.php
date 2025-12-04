<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::injectOn
 */
class InjectOnTest extends TestCase
{
    /**
     * @test
     *
     * @testdox injectOn() calls setter injection
     *
     * @covers \Container::injectOn */
    public function injectCallsSetters(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() skips methods without setters
     *
     * @covers \Container::injectOn */
    public function injectSkipsNonSetters(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() injects only class-typed arguments
     *
     * @covers \Container::injectOn */
    public function injectOnlyClassTypes(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() ignores unresolvable dependencies
     *
     * @covers \Container::injectOn */
    public function injectIgnoresUnresolvable(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() does not inject static methods
     *
     * @covers \Container::injectOn */
    public function injectSkipsStatic(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() resolves multiple setter methods
     *
     * @covers \Container::injectOn */
    public function injectMultipleSetters(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() supports deeper dependency resolution
     *
     * @covers \Container::injectOn */
    public function injectResolvesNested(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox injectOn() returns the same instance
     *
     * @covers \Container::injectOn */
    public function injectReturnsOriginal(): void
    {
        $this->assertTrue(false);
    }
}
