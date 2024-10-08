<?php

declare(strict_types=1);

namespace System\Text\Cache;

use PHPUnit\Framework\TestCase;
use System\Cache\CacheInterface;
use System\Cache\CacheManager;
use System\Cache\Storage\ArrayStorage;

class CacheManagerTest extends TestCase
{
    public function testSetDefaultDriver(): void
    {
        $cache = new CacheManager();
        $cache->setDefaultDriver(new ArrayStorage());
        $this->assertInstanceOf(CacheInterface::class, $cache->driver());

        $this->assertTrue($cache->set('key1', 'value1'));
        $this->assertEquals('value1', $cache->get('key1'));
    }

    public function testDriver(): void
    {
        $cache = new CacheManager();
        $cache->setDriver('array2', fn (): CacheInterface => new ArrayStorage());
        $this->assertInstanceOf(CacheInterface::class, $cache->driver('array2'));

        $this->assertTrue($cache->driver('array2')->set('key1', 'value1'));
        $this->assertEquals('value1', $cache->driver('array2')->get('key1'));
    }
}
