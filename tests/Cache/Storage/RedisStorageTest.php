<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\RedisStorage;
use System\Redis\RedisInterface;

final class RedisStorageTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /** @test */
    public function itCanGetCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('get')->with('key')->andReturn(serialize('value'));

        $storage = new RedisStorage($redis);

        $this->assertEquals('value', $storage->get('key'));
    }

    /** @test */
    public function itCanGetDefaultIfKeyNotFound()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('get')->with('key')->andReturn(false);

        $storage = new RedisStorage($redis);

        $this->assertEquals('default', $storage->get('key', 'default'));
    }

    /** @test */
    public function itCanSetCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('set')->with('key', serialize('value'), 3600)->andReturn(true);

        $storage = new RedisStorage($redis);

        $this->assertTrue($storage->set('key', 'value', 3600));
    }

    /** @test */
    public function itCanDeleteCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('del')->with('key')->andReturn(1);

        $storage = new RedisStorage($redis);

        $this->assertTrue($storage->delete('key'));
    }

    /** @test */
    public function itCanClearCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('command')->with('flushdb')->andReturn(true);

        $storage = new RedisStorage($redis);

        $this->assertTrue($storage->clear());
    }

    /** @test */
    public function itCanCheckIfKeyExists()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('exists')->with('key')->andReturn(true);

        $storage = new RedisStorage($redis);

        $this->assertTrue($storage->has('key'));
    }

    /** @test */
    public function itCanIncrementCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        // First it checks if key exists
        $redis->shouldReceive('exists')->with('key')->andReturn(true);
        // Then it gets the value
        $redis->shouldReceive('get')->with('key')->andReturn(serialize(10));
        // Then it sets the incremented value
        $redis->shouldReceive('set')->with('key', serialize(11), 3600)->andReturn(true);

        $storage = new RedisStorage($redis);

        $this->assertEquals(11, $storage->increment('key', 1));
    }

    /** @test */
    public function itCanDecrementCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('exists')->with('key')->andReturn(true);
        $redis->shouldReceive('get')->with('key')->andReturn(serialize(10));
        $redis->shouldReceive('set')->with('key', serialize(9), 3600)->andReturn(true);

        $storage = new RedisStorage($redis);

        $this->assertEquals(9, $storage->decrement('key', 1));
    }

    /** @test */
    public function itCanRememberCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('get')->with('key')->andReturn(false);
        $redis->shouldReceive('set')->with('key', serialize('value'), 3600)->andReturn(true);

        $storage = new RedisStorage($redis);

        $result = $storage->remember('key', 3600, fn () => 'value');

        $this->assertEquals('value', $result);
    }

    /** @test */
    public function itCanGetMultipleCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('get')->with('key1')->andReturn(serialize('value1'));
        $redis->shouldReceive('get')->with('key2')->andReturn(serialize('value2'));

        $storage = new RedisStorage($redis);

        $results = $storage->getMultiple(['key1', 'key2']);

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $results);
    }

    /** @test */
    public function itCanSetMultipleCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('set')->with('key1', serialize('value1'), 3600)->andReturn(true);
        $redis->shouldReceive('set')->with('key2', serialize('value2'), 3600)->andReturn(true);

        $storage = new RedisStorage($redis);

        $this->assertTrue($storage->setMultiple(['key1' => 'value1', 'key2' => 'value2'], 3600));
    }

    /** @test */
    public function itCanDeleteMultipleCache()
    {
        $redis = \Mockery::mock(RedisInterface::class);
        $redis->shouldReceive('del')->with('key1')->andReturn(1);
        $redis->shouldReceive('del')->with('key2')->andReturn(1);

        $storage = new RedisStorage($redis);

        $this->assertTrue($storage->deleteMultiple(['key1', 'key2']));
    }
}
