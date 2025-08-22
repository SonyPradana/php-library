<?php

declare(strict_types=1);

namespace Tests\System\RateLimiter;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\ArrayStorage;
use System\RateLimiter\Interfaces\RateLimiterInterface;
use System\RateLimiter\RateLimiterFactory;

class RateLimiterFactoryTest extends TestCase
{
    /** @test */
    public function itCanCreateRateLimiter(): void
    {
        $factory = new RateLimiterFactory(new ArrayStorage());

        $this->assertInstanceOf(
            RateLimiterInterface::class,
            $factory->createFixedWindow(10, 60)
        );

        $this->assertInstanceOf(
            RateLimiterInterface::class,
            $factory->createNoLimiter()
        );
    }
}
