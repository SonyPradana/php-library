<?php

declare(strict_types=1);

namespace System\Test\Container;

use System\Test\Container\TestContainer as TestCase;

/**
 * @covers \Container::enableCache
 * @covers \Container::clearCache
 */
class CacheTest extends TestCase
{
    /**
     * @test
     *
     * @testdox Cache can be enabled
     *
     * @covers \Container::enableCache */
    public function cacheEnable(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Cache disabled yields no caching
     *
     * @covers \Container::enableCache */
    public function cacheDisable(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Cache stores resolved entries
     *
     * @covers \Container::enableCache */
    public function cacheStoresValues(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox clearCache() empties resolution cache
     *
     * @covers \Container::clearCache */
    public function cacheClear(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Cache isolates between get() and make()
     *
     * @covers \Container::enableCache */
    public function cacheIsolatedBetweenGetAndMake(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @test
     *
     * @testdox Cache does not break alias resolution
     *
     * @covers \Container::enableCache */
    public function cacheAliasSafe(): void
    {
        $this->assertTrue(false);
    }
}
