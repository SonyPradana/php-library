<?php

declare(strict_types=1);

namespace System\Test\Container;

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
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox get() throws when entry not found
     *
     *  @covers \Container::get */
    public function getNotFound(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox make() always returns new instance
     *
     *  @covers \Container::make */
    public function makeFreshInstance(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox make() accepts parameters
     *
     *  @covers \Container::make */
    public function makeWithParameters(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox get() resolves via closure binding
     *
     *  @covers \Container::get */
    public function getClosure(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox make() resolves via closure
     *
     *  @covers \Container::make */
    public function makeClosure(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox get() respects alias resolution
     *
     *  @covers \Container::get */
    public function getViaAlias(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox make() respects alias resolution
     *
     *  @covers \Container::make */
    public function makeViaAlias(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox get() caches resolved singleton
     *
     *  @covers \Container::get */
    public function getSingletonCached(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox get() resolves deep dependencies
     *
     *  @covers \Container::get */
    public function getResolvesRecursiveDependencies(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox make() resolves deep dependencies
     *
     *  @covers \Container::make */
    public function makeResolvesRecursiveDependencies(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox make() throws on unresolvable dependency
     *
     *  @covers \Container::make */
    public function makeUnresolvableDependency(): void
    {
        $this->assertTrue(false);
    }
}
