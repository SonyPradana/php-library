<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Exceptions\InvalidCacheArgumentException;
use System\Cache\Exceptions\UnsupportedCacheDriverException;
use System\Cache\Storage\MemcachedConnector;
use System\Cache\Storage\MemcachedStorage;

/**
 * @group memcached
 */
class MemcachedStorageTest extends TestCase
{
    private \Memcached $memcached;

    private MemcachedStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('\Memcached')) {
            $this->markTestSkipped('Memcached extension is not installed.');
        }

        $connector       = new MemcachedConnector();
        $this->memcached = $connector->connect([
            ['host' => '127.0.0.1', 'port' => 11211],
        ]);

        if (count($this->memcached->getServerList()) === 0) {
            $this->markTestSkipped('Memcached server is not running.');
        }

        $this->storage = new MemcachedStorage($this->memcached);
        $this->storage->clear();
    }

    protected function tearDown(): void
    {
        if (class_exists('\Memcached')) {
            $this->storage->clear();
        }

        parent::tearDown();
    }

    /**
     * @test
     *
     * @testdox it can get value from cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::get
     */
    public function itCanGetValueFromCache(): void
    {
        $this->storage->set('key1', 'value1');

        $this->assertEquals('value1', $this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox it returns default value when key is not found
     *
     * @covers \System\Cache\Storage\MemcachedStorage::get
     */
    public function itReturnsDefaultValueWhenKeyIsNotFound(): void
    {
        $this->assertEquals('default', $this->storage->get('not-found', 'default'));
    }

    /**
     * @test
     *
     * @testdox it can set value to cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::set
     * @covers \System\Cache\Storage\MemcachedStorage::calculateExpiration
     */
    public function itCanSetValueToCache(): void
    {
        $this->assertTrue($this->storage->set('key1', 'value1', 3600));
        $this->assertEquals('value1', $this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox it can delete value from cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::delete
     */
    public function itCanDeleteValueFromCache(): void
    {
        $this->storage->set('key1', 'value1');
        $this->assertTrue($this->storage->delete('key1'));
        $this->assertNull($this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox it can clear all cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::clear
     */
    public function itCanClearAllCache(): void
    {
        $this->storage->set('key1', 'value1');
        $this->assertTrue($this->storage->clear());
        $this->assertNull($this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox it can get multiple values from cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::getMultiple
     */
    public function itCanGetMultipleValuesFromCache(): void
    {
        $this->storage->set('key1', 'value1');
        $this->storage->set('key2', 'value2');

        $result = $this->storage->getMultiple(['key1', 'key2', 'key3'], 'default');

        $this->assertEquals([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'default',
        ], $result);
    }

    /**
     * @test
     *
     * @testdox it can set multiple values to cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::setMultiple
     */
    public function itCanSetMultipleValuesToCache(): void
    {
        $this->assertTrue($this->storage->setMultiple(['key1' => 'value1', 'key2' => 'value2'], 3600));
        $this->assertEquals('value1', $this->storage->get('key1'));
        $this->assertEquals('value2', $this->storage->get('key2'));
    }

    /**
     * @test
     *
     * @testdox it can check if key exists in cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::has
     */
    public function itCanCheckIfKeyExistsInCache(): void
    {
        $this->storage->set('key1', 'value1');

        $this->assertTrue($this->storage->has('key1'));
        $this->assertFalse($this->storage->has('not-found'));
    }

    /**
     * @test
     *
     * @testdox it can increment value in cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::increment
     */
    public function itCanIncrementValueInCache(): void
    {
        $this->storage->set('key1', 5);
        $this->assertEquals(6, $this->storage->increment('key1', 1));
        $this->assertEquals(6, $this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox it can increment non-existent value in cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::increment
     */
    public function itCanIncrementNonExistentValueInCache(): void
    {
        $this->assertEquals(1, $this->storage->increment('new-key', 1));
        $this->assertEquals(1, $this->storage->get('new-key'));
    }

    /**
     * @test
     *
     * @testdox it can decrement value in cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::decrement
     */
    public function itCanDecrementValueInCache(): void
    {
        $this->storage->set('key1', 5);
        $this->assertEquals(4, $this->storage->decrement('key1', 1));
        $this->assertEquals(4, $this->storage->get('key1'));
    }

    /**
     * @test
     *
     * @testdox it can remember value in cache
     *
     * @covers \System\Cache\Storage\MemcachedStorage::remember
     */
    public function itCanRememberValueInCache(): void
    {
        $result = $this->storage->remember('rem-key', 3600, function () {
            return 'remembered';
        });

        $this->assertEquals('remembered', $result);
        $this->assertEquals('remembered', $this->storage->get('rem-key'));
    }

    /**
     * @test
     *
     * @testdox it normalizes invalid keys (long or spaces)
     *
     * @covers \System\Cache\Storage\MemcachedStorage::normalizeKey
     */
    public function itNormalizesInvalidKeys(): void
    {
        // Key with space
        $this->assertTrue($this->storage->set('key with space', 'value1'));
        $this->assertEquals('value1', $this->storage->get('key with space'));

        // Very long key
        $longKey = str_repeat('a', 300);
        $this->assertTrue($this->storage->set($longKey, 'value2'));
        $this->assertEquals('value2', $this->storage->get($longKey));
    }

    /**
     * @test
     *
     * @testdox It throws InvalidCacheArgumentException if memcached is not an instance of \Memcached
     *
     * @covers \System\Cache\Storage\MemcachedStorage::__construct
     */
    public function itThrowsInvalidCacheArgumentExceptionWhenMemcachedIsNotInstanceOfMemcached(): void
    {
        $this->expectException(InvalidCacheArgumentException::class);
        new MemcachedStorage(new \stdClass());
    }

    /**
     * @test
     *
     * @testdox It throws UnsupportedCacheDriverException if MemcachedException is thrown
     *
     * @covers \System\Cache\Storage\MemcachedStorage::get
     */
    public function itThrowsUnsupportedCacheDriverExceptionWhenMemcachedExceptionIsThrown(): void
    {
        $memcachedMock = $this->createMock(\Memcached::class);
        $memcachedMock->method('get')->willThrowException(new \MemcachedException('Test exception'));

        $storage = new MemcachedStorage($memcachedMock);

        $this->expectException(UnsupportedCacheDriverException::class);
        $storage->get('key');
    }
}
