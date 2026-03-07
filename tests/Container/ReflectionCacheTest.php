<?php

declare(strict_types=1);

namespace System\Test\Container;

use PHPUnit\Framework\TestCase;
use System\Container\ReflectionCache;

/**
 * @covers \System\Container\ReflectionCache
 */
final class ReflectionCacheTest extends TestCase
{
    private ReflectionCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new ReflectionCache();
    }

    /** @test */
    public function itGetsAndCachesReflectionClass(): void
    {
        $callCount = 0;
        $creator   = function () use (&$callCount) {
            $callCount++;

            return new \ReflectionClass(\stdClass::class);
        };

        $result1 = $this->cache->getReflectionClass(\stdClass::class, $creator);
        $result2 = $this->cache->getReflectionClass(\stdClass::class, $creator);

        $this->assertSame($result1, $result2);
        $this->assertEquals(1, $callCount, 'Creator should only be called once.');
        $this->assertInstanceOf(\ReflectionClass::class, $result1);
    }

    /** @test */
    public function itGetsAndCachesReflectionMethod(): void
    {
        $callCount = 0;
        $creator   = function () use (&$callCount) {
            $callCount++;

            return new \ReflectionMethod(\DateTime::class, 'getTimestamp');
        };

        $result1 = $this->cache->getReflectionMethod(\DateTime::class, 'getTimestamp', $creator);
        $result2 = $this->cache->getReflectionMethod(\DateTime::class, 'getTimestamp', $creator);

        $this->assertSame($result1, $result2);
        $this->assertEquals(1, $callCount, 'Creator should only be called once.');
        $this->assertInstanceOf(\ReflectionMethod::class, $result1);
    }

    /** @test */
    public function itGetsAndCachesConstructorParameters(): void
    {
        $callCount = 0;
        $fixture   = new class {
            public function __construct(int $time = 0, string $name = '')
            {
            }
        };
        $ref    = new \ReflectionClass($fixture);
        $params = $ref->getConstructor()->getParameters();

        $creator = function () use (&$callCount, $params) {
            $callCount++;

            return $params;
        };

        $result1 = $this->cache->getConstructorParameters($fixture::class, $creator);
        $result2 = $this->cache->getConstructorParameters($fixture::class, $creator);

        $this->assertSame($result1, $result2);
        $this->assertEquals(1, $callCount, 'Creator should only be called once.');
        $this->assertEquals($params, $result1);
    }

    /** @test */
    public function itClearsAllCaches(): void
    {
        $callCount = 0;
        $creator   = function () use (&$callCount) {
            $callCount++;

            return new \ReflectionClass(\stdClass::class);
        };

        // Populate the cache
        $this->cache->getReflectionClass(\stdClass::class, $creator);
        $this->assertEquals(1, $callCount);

        // Clear the cache
        $this->cache->clear();

        // Try to get the item again
        $this->cache->getReflectionClass(\stdClass::class, $creator);
        $this->assertEquals(2, $callCount, 'Creator should be called again after clearing the cache.');
    }
}
