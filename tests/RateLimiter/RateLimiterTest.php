<?php

declare(strict_types=1);

namespace Tests\System\RateLimiter;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\ArrayStorage;
use System\RateLimiter\RateLimiter;
use System\RateLimiter\RateLimiter\FixedWindow;

class RateLimiterTest extends TestCase
{
    private RateLimiter $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rateLimiter = new RateLimiter(
            new FixedWindow(
                cache: new ArrayStorage(),
                limit: 1,
                windowSeconds: 60
            )
        );
    }

    /** @test */
    public function isBlocked(): void
    {
        $this->assertFalse($this->rateLimiter->isBlocked('key', 1, 1));

        $this->rateLimiter->consume('key');

        $this->assertTrue($this->rateLimiter->isBlocked('key', 1, 1));
    }

    /** @test */
    public function consume(): void
    {
        $this->assertEquals(1, $this->rateLimiter->consume('key'));
    }

    /** @test */
    public function getCountLeft(): void
    {
        $this->assertEquals(0, $this->rateLimiter->getCount('key'));

        $this->rateLimiter->consume('key');

        $this->assertEquals(1, $this->rateLimiter->getCount('key'));
    }

    /** @test */
    public function getRetryAfter(): void
    {
        $this->assertGreaterThan(0, $this->rateLimiter->getRetryAfter('key'));
    }

    /** @test */
    public function remaining(): void
    {
        $this->assertEquals(1, $this->rateLimiter->remaining('key', 1));

        $this->rateLimiter->consume('key');

        $this->assertEquals(0, $this->rateLimiter->remaining('key', 1));
    }

    /** @test */
    public function reset(): void
    {
        $this->rateLimiter->consume('key');

        $this->rateLimiter->reset('key');

        $this->assertEquals(0, $this->rateLimiter->getCount('key'));
    }
}
