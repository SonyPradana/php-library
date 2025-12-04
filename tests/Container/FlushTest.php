<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::flush
 */
class FlushTest extends TestCase
{
    /**
     * @test
     *
     * @testdox flush() removes all bindings
     *
     * @covers \Container::flush */
    public function flushRemovesBindings(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox flush() clears singleton cache
     *
     * @covers \Container::flush */
    public function flushClearsCache(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox flush() clears alias map
     *
     * @covers \Container::flush */
    public function flushClearsAlias(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox flush() produces empty clean container
     *
     * @covers \Container::flush */
    public function flushResetsContainer(): void
    {
        $this->assertTrue(false);
    }
}
