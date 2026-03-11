<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Exceptions\InvalidCacheArgumentException;
use System\Cache\Exceptions\UnsupportedCacheDriverException;
use System\Cache\Storage\PdoStorage;

/**
 * @group database
 *
 * @covers \System\Cache\Storage\PdoStorage
 */
class PdoStorageTest extends TestCase
{
    private \PDO $pdo;
    private PdoStorage $storage;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->exec('
            CREATE TABLE cache (
                "key" VARCHAR(255) PRIMARY KEY,
                "value" TEXT,
                "expiration" INT
            )
        ');

        $this->storage = new PdoStorage($this->pdo, 'cache', 60);
    }

    /**
     * @test
     *
     * @testdox it can get and set cache
     *
     * @covers \System\Cache\Storage\PdoStorage::get
     * @covers \System\Cache\Storage\PdoStorage::set
     * @covers \System\Cache\Storage\PdoStorage::quoteIdentifier
     */
    public function itCanGetAndSetCache()
    {
        $this->assertTrue($this->storage->set('foo', 'bar'));
        $this->assertEquals('bar', $this->storage->get('foo'));
    }

    /**
     * @test
     *
     * @testdox it should return null if cache expired
     *
     * @covers \System\Cache\Storage\PdoStorage::get
     */
    public function itShouldReturnNullIfCacheExpired()
    {
        $this->storage->set('foo', 'bar', -1);
        $this->assertNull($this->storage->get('foo'));
    }

    /**
     * @test
     *
     * @testdox it can delete cache
     *
     * @covers \System\Cache\Storage\PdoStorage::delete
     */
    public function itCanDeleteCache()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->delete('foo'));
        $this->assertNull($this->storage->get('foo'));
    }

    /**
     * @test
     *
     * @testdox it can clear all cache
     *
     * @covers \System\Cache\Storage\PdoStorage::clear
     */
    public function itCanClearAllCache()
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('baz', 'qux');
        $this->assertTrue($this->storage->clear());
        $this->assertNull($this->storage->get('foo'));
        $this->assertNull($this->storage->get('baz'));
    }

    /**
     * @test
     *
     * @testdox it can check if cache exists
     *
     * @covers \System\Cache\Storage\PdoStorage::has
     */
    public function itCanCheckIfCacheExists()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->has('foo'));
        $this->assertFalse($this->storage->has('not_found'));
    }

    /**
     * @test
     *
     * @testdox it can increment cache value
     *
     * @covers \System\Cache\Storage\PdoStorage::increment
     */
    public function itCanIncrementCacheValue()
    {
        $this->storage->set('num', 1);
        $this->assertEquals(2, $this->storage->increment('num', 1));
        $this->assertEquals(2, $this->storage->get('num'));
    }

    /**
     * @test
     *
     * @testdox it can decrement cache value
     *
     * @covers \System\Cache\Storage\PdoStorage::decrement
     */
    public function itCanDecrementCacheValue()
    {
        $this->storage->set('num', 10);
        $this->assertEquals(7, $this->storage->decrement('num', 3));
        $this->assertEquals(7, $this->storage->get('num'));
    }

    /**
     * @test
     *
     * @testdox it can remember cache value
     *
     * @covers \System\Cache\Storage\PdoStorage::remember
     */
    public function itCanRememberCacheValue()
    {
        $this->assertNull($this->storage->get('rem'));
        $value = $this->storage->remember('rem', 60, fn() => 'remembered');
        $this->assertEquals('remembered', $value);
        $this->assertEquals('remembered', $this->storage->get('rem'));
    }

    /**
     * @test
     *
     * @testdox it can handle multiple cache operations
     *
     * @covers \System\Cache\Storage\PdoStorage::getMultiple
     * @covers \System\Cache\Storage\PdoStorage::setMultiple
     * @covers \System\Cache\Storage\PdoStorage::deleteMultiple
     */
    public function itCanHandleMultipleCacheOperations()
    {
        $values = ['a' => 1, 'b' => 2];
        $this->assertTrue($this->storage->setMultiple($values));

        $get = $this->storage->getMultiple(['a', 'b', 'c'], 'default');
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 'default'], (array) $get);

        $this->assertTrue($this->storage->deleteMultiple(['a', 'b']));
        $this->assertNull($this->storage->get('a'));
        $this->assertNull($this->storage->get('b'));
    }

    /**
     * @test
     *
     * @testdox It throws InvalidCacheArgumentException if increment value is not integer
     *
     * @covers \System\Cache\Storage\PdoStorage::increment
     */
    public function itThrowsInvalidCacheArgumentExceptionWhenIncrementValueIsNotInteger(): void
    {
        $this->storage->set('key', 'not an integer');
        $this->expectException(InvalidCacheArgumentException::class);
        $this->storage->increment('key', 1);
    }
}
