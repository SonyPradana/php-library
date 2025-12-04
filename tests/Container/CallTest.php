<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::call
 */
class CallTest extends TestCase
{
    /**
     * @test
     *
     * @testdox call() invokes a function
     *
     * @covers \Container::call */
    public function callFunction(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox call() invokes method array syntax
     *
     * @covers \Container::call */
    public function callClassMethod(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox call() invokes static method string syntax
     *
     * @covers \Container::call */
    public function callStaticMethod(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox call() injects dependencies in parameters
     *
     * @covers \Container::call */
    public function callInjectsDependencies(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox call() merges user parameters and auto injection
     *
     * @covers \Container::call */
    public function callWithCustomParameters(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox call() resolves callable from container binding
     *
     * @covers \Container::call */
    public function callResolvesViaContainer(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox call() throws on unresolvable param
     *
     * @covers \Container::call */
    public function callUnresolvableParameter(): void
    {
        $this->assertTrue(false);
    }
}
