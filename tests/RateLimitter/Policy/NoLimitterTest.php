<?php

declare(strict_types=1);

namespace Tests\System\RateLimitter\Policy;

use PHPUnit\Framework\TestCase;
use System\RateLimitter\RateLimtter\NoLimitter;

class NoLimitterTest extends TestCase
{
    /** @test */
    public function itCanConsumeTokensWithinTheLimit()
    {
        $limiter   = new NoLimitter();
        $rateLimit = $limiter->consume('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(0, $rateLimit->getConsumed());
        $this->assertEquals(PHP_INT_MAX, $rateLimit->getRemaining());
    }

    /** @test */
    public function itNeverBlocksWhenConsumingTokensExceedsTheLimit()
    {
        $limiter = new NoLimitter();

        for ($i = 0; $i < 5; $i++) {
            $limiter->consume('test_key');
        }

        $rateLimit = $limiter->consume('test_key');

        $this->assertFalse($rateLimit->isBlocked());
        $this->assertEquals(0, $rateLimit->getConsumed());
        $this->assertEquals(PHP_INT_MAX, $rateLimit->getRemaining());
    }

    /** @test */
    // public function itCanPeekAtTheRateLimitStatus()
    // {
    //     $limiter = new NoLimiter();

    //     $this->cache->set('test_key:fw:' . floor(now()->timestamp / 60), 3);

    //     $rateLimit = $limiter->peek('test_key');

    //     $this->assertFalse($rateLimit->isBlocked());
    //     $this->assertEquals(3, $rateLimit->getConsumed());
    //     $this->assertEquals(2, $rateLimit->getRemaining());
    // }

    /** @test */
    public function itCanResetTheRateLimit()
    {
        $limiter = new NoLimitter();

        $limiter->consume('test_key');
        $limiter->reset('test_key');

        $rateLimit = $limiter->peek('test_key');

        $this->assertEquals(0, $rateLimit->getConsumed());
    }
}
