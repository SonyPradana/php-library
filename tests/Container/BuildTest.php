<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

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
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build resolves constructor dependencies
     *
     * @covers \Container::build */
    public function buildWithDependencies(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build with parameters overrides constructor defaults
     *
     * @covers \Container::build */
    public function buildWithCustomParameters(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build from closure returns result
     *
     * @covers \Container::build */
    public function buildFromClosure(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build fails on missing dependency
     *
     * @covers \Container::build */
    public function buildMissingDependency(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build handles circular dependency error
     *
     * @covers \Container::build */
    public function buildCircularDependency(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build resolves typed constructor params
     *
     * @covers \Container::build */
    public function buildTypedConstructor(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Build resolves union types or throws
     *
     * @covers \Container::build */
    public function buildUnionTypeConstructor(): void
    {
        $this->assertTrue(false);
    }
}
