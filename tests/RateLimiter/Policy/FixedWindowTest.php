<?php

declare(strict_types=1);

namespace Tests\System\RateLimiter\Policy;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\ArrayStorage;
use System\RateLimiter\RateLimiter\FixedWindow;

class FixedWindowTest extends TestCase
{
    private ArrayStorage $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new ArrayStorage();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cache->clear();
    }

    /** @test */
    public function itCanConsumeTokensWithinTheLimit()
    {
        $limiter   = new FixedWindow($this->cache, 5, 60);
        $rateLimit = $limiter->consume('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(1, $rateLimit->getConsumed());
        $this->assertEquals(4, $rateLimit->getRemaining());
    }

    /** @test */
    public function itBlocksWhenConsumingTokensExceedsTheLimit()
    {
        $limiter = new FixedWindow($this->cache, 5, 60);

        for ($i = 0; $i < 5; $i++) {
            $limiter->consume('test_key');
        }

        $rateLimit = $limiter->consume('test_key');

        $this->assertTrue($rateLimit->isBlocked());
        $this->assertEquals(5, $rateLimit->getConsumed());
        $this->assertEquals(0, $rateLimit->getRemaining());
    }

    /** @test */
    public function itCanPeekAtTheRateLimitStatus()
    {
        $limiter = new FixedWindow($this->cache, 5, 60);

        $this->cache->set('test_key:fw:' . floor(now()->timestamp / 60), 3);

        $rateLimit = $limiter->peek('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(3, $rateLimit->getConsumed());
        $this->assertEquals(2, $rateLimit->getRemaining());
    }

    /** @test */
    public function itCanResetTheRateLimit()
    {
        $limiter = new FixedWindow($this->cache, 5, 60);

        $limiter->consume('test_key');
        $limiter->reset('test_key');

        $rateLimit = $limiter->peek('test_key');

        $this->assertEquals(0, $rateLimit->getConsumed());
    }
}
