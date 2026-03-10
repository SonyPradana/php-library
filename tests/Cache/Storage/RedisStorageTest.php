<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\RedisStorage;
use System\Redis\Redis;

/**
 * @coversDefaultClass \System\Cache\Storage\RedisStorage
 *
 * @group redis
 */
final class RedisStorageTest extends TestCase
{
    /** @var Redis|null */
    private $redis;

    /** @var RedisStorage|null */
    private $storage;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded.');
        }

        try {
            $this->redis = new Redis([
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'database' => 2,
            ]);
            $this->redis->command('ping');
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not connect to Redis server: ' . $e->getMessage());
        }

        $this->redis->flushdb();
        $this->storage = new RedisStorage($this->redis);
    }

    protected function tearDown(): void
    {
        if ($this->redis) {
            $this->redis->flushdb();
            $this->redis->disconnect();
        }
        $this->redis   = null;
        $this->storage = null;
        parent::tearDown();
    }

    /**
     * @test
     *
     * @testdox it can set and get cache
     *
     * @covers ::set
     * @covers ::get
     * @covers ::calculateTTLInSeconds
     */
    public function itCanSetAndGetCache()
    {
        $this->assertTrue($this->storage->set('key', 'value'));
        $this->assertEquals('value', $this->storage->get('key'));
    }

    /**
     * @test
     *
     * @testdox it can get default value if key not found
     *
     * @covers ::get
     */
    public function itCanGetDefaultIfKeyNotFound()
    {
        $this->assertEquals('default', $this->storage->get('key', 'default'));
    }

    /**
     * @test
     *
     * @testdox it can delete cache
     *
     * @covers ::delete
     */
    public function itCanDeleteCache()
    {
        $this->storage->set('key', 'value');
        $this->assertTrue($this->storage->delete('key'));
        $this->assertNull($this->storage->get('key'));
    }

    /**
     * @test
     *
     * @testdox it can clear cache
     *
     * @covers ::clear
     */
    public function itCanClearCache()
    {
        $this->storage->set('key1', 'value1');
        $this->storage->set('key2', 'value2');
        $this->assertTrue($this->storage->clear());
        $this->assertNull($this->storage->get('key1'));
        $this->assertNull($this->storage->get('key2'));
    }

    /**
     * @test
     *
     * @testdox it can check if key exists
     *
     * @covers ::has
     */
    public function itCanCheckIfKeyExists()
    {
        $this->storage->set('key', 'value');
        $this->assertTrue($this->storage->has('key'));
        $this->assertFalse($this->storage->has('not_found'));
    }

    /**
     * @test
     *
     * @testdox it can increment cache value
     *
     * @covers ::increment
     */
    public function itCanIncrementCache()
    {
        $this->storage->set('key', 10);
        $this->assertEquals(11, $this->storage->increment('key', 1));
        $this->assertEquals(11, $this->storage->get('key'));
    }

    /**
     * @test
     *
     * @testdox it can decrement cache value
     *
     * @covers ::decrement
     */
    public function itCanDecrementCache()
    {
        $this->storage->set('key', 10);
        $this->assertEquals(9, $this->storage->decrement('key', 1));
        $this->assertEquals(9, $this->storage->get('key'));
    }

    /**
     * @test
     *
     * @testdox it can remember cache value
     *
     * @covers ::remember
     */
    public function itCanRememberCache()
    {
        $result = $this->storage->remember('key', 3600, fn () => 'value');
        $this->assertEquals('value', $result);
        $this->assertEquals('value', $this->storage->get('key'));
    }

    /**
     * @test
     *
     * @testdox it can get multiple cache values
     *
     * @covers ::getMultiple
     */
    public function itCanGetMultipleCache()
    {
        $this->storage->set('key1', 'value1');
        $this->storage->set('key2', 'value2');

        $results = $this->storage->getMultiple(['key1', 'key2']);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $results);
    }

    /**
     * @test
     *
     * @testdox it can set multiple cache values
     *
     * @covers ::setMultiple
     */
    public function itCanSetMultipleCache()
    {
        $this->assertTrue($this->storage->setMultiple(['key1' => 'value1', 'key2' => 'value2'], 3600));
        $this->assertEquals('value1', $this->storage->get('key1'));
        $this->assertEquals('value2', $this->storage->get('key2'));
    }

    /**
     * @test
     *
     * @testdox it can delete multiple cache values
     *
     * @covers ::deleteMultiple
     */
    public function itCanDeleteMultipleCache()
    {
        $this->storage->set('key1', 'value1');
        $this->storage->set('key2', 'value2');

        $this->assertTrue($this->storage->deleteMultiple(['key1', 'key2']));
        $this->assertNull($this->storage->get('key1'));
        $this->assertNull($this->storage->get('key2'));
    }

    /**
     * @test
     *
     * @testdox it should not unserialize objects by default for security
     *
     * @covers ::get
     */
    public function itShouldNotUnserializeObjectsByDefaultForSecurity()
    {
        $obj      = new \stdClass();
        $obj->foo = 'bar';
        $this->storage->set('key', $obj);

        $result = $this->storage->get('key');

        $this->assertInstanceOf('__PHP_Incomplete_Class', $result);
    }

    /**
     * @test
     *
     * @testdox it should handle expiration using DateInterval
     *
     * @covers ::calculateTTLInSeconds
     * @covers ::set
     */
    public function itShouldHandleExpirationWithDateInterval()
    {
        $interval = new \DateInterval('PT1S');
        $this->assertTrue($this->storage->set('expire_key', 'value', $interval));
        $this->assertEquals('value', $this->storage->get('expire_key'));

        sleep(2);

        $this->assertNull($this->storage->get('expire_key'));
    }
}
