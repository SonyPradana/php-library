<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\PdoStorage;

/**
 * @group database
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
     * @testdox it can get and set cache
     * @covers \System\Cache\Storage\PdoStorage::get
     * @covers \System\Cache\Storage\PdoStorage::set
     * @covers \System\Cache\Storage\PdoStorage::quoteIdentifier
     */
    public function it_can_get_and_set_cache()
    {
        $this->assertTrue($this->storage->set('foo', 'bar'));
        $this->assertEquals('bar', $this->storage->get('foo'));
    }

    /**
     * @test
     * @testdox it should return null if cache expired
     * @covers \System\Cache\Storage\PdoStorage::get
     */
    public function it_should_return_null_if_cache_expired()
    {
        $this->storage->set('foo', 'bar', -1);
        $this->assertNull($this->storage->get('foo'));
    }

    /**
     * @test
     * @testdox it can delete cache
     * @covers \System\Cache\Storage\PdoStorage::delete
     */
    public function it_can_delete_cache()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->delete('foo'));
        $this->assertNull($this->storage->get('foo'));
    }

    /**
     * @test
     * @testdox it can clear all cache
     * @covers \System\Cache\Storage\PdoStorage::clear
     */
    public function it_can_clear_all_cache()
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('baz', 'qux');
        $this->assertTrue($this->storage->clear());
        $this->assertNull($this->storage->get('foo'));
        $this->assertNull($this->storage->get('baz'));
    }

    /**
     * @test
     * @testdox it can check if cache exists
     * @covers \System\Cache\Storage\PdoStorage::has
     */
    public function it_can_check_if_cache_exists()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->has('foo'));
        $this->assertFalse($this->storage->has('not_found'));
    }

    /**
     * @test
     * @testdox it can increment cache value
     * @covers \System\Cache\Storage\PdoStorage::increment
     */
    public function it_can_increment_cache_value()
    {
        $this->storage->set('num', 1);
        $this->assertEquals(2, $this->storage->increment('num', 1));
        $this->assertEquals(2, $this->storage->get('num'));
    }

    /**
     * @test
     * @testdox it can decrement cache value
     * @covers \System\Cache\Storage\PdoStorage::decrement
     */
    public function it_can_decrement_cache_value()
    {
        $this->storage->set('num', 10);
        $this->assertEquals(7, $this->storage->decrement('num', 3));
        $this->assertEquals(7, $this->storage->get('num'));
    }

    /**
     * @test
     * @testdox it can remember cache value
     * @covers \System\Cache\Storage\PdoStorage::remember
     */
    public function it_can_remember_cache_value()
    {
        $this->assertNull($this->storage->get('rem'));
        $value = $this->storage->remember('rem', 60, fn () => 'remembered');
        $this->assertEquals('remembered', $value);
        $this->assertEquals('remembered', $this->storage->get('rem'));
    }

    /**
     * @test
     * @testdox it can handle multiple cache operations
     * @covers \System\Cache\Storage\PdoStorage::getMultiple
     * @covers \System\Cache\Storage\PdoStorage::setMultiple
     * @covers \System\Cache\Storage\PdoStorage::deleteMultiple
     */
    public function it_can_handle_multiple_cache_operations()
    {
        $values = ['a' => 1, 'b' => 2];
        $this->assertTrue($this->storage->setMultiple($values));

        $get = $this->storage->getMultiple(['a', 'b', 'c'], 'default');
        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 'default'], (array) $get);

        $this->assertTrue($this->storage->deleteMultiple(['a', 'b']));
        $this->assertNull($this->storage->get('a'));
        $this->assertNull($this->storage->get('b'));
    }
}
