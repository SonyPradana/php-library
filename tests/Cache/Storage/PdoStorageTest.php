<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\PdoStorage;

class PdoStorageTest extends TestCase
{
    private \PDO $pdo;
    private PdoStorage $storage;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->exec('
            CREATE TABLE cache (
                key VARCHAR(255) PRIMARY KEY,
                value TEXT,
                expiration INT
            )
        ');

        $this->storage = new PdoStorage($this->pdo, 'cache', 60);
    }

    public function testGetAndSet()
    {
        $this->assertTrue($this->storage->set('foo', 'bar'));
        $this->assertEquals('bar', $this->storage->get('foo'));
    }

    public function testGetExpired()
    {
        $this->storage->set('foo', 'bar', -1);
        $this->assertNull($this->storage->get('foo'));
    }

    public function testDelete()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->delete('foo'));
        $this->assertNull($this->storage->get('foo'));
    }

    public function testClear()
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('baz', 'qux');
        $this->assertTrue($this->storage->clear());
        $this->assertNull($this->storage->get('foo'));
        $this->assertNull($this->storage->get('baz'));
    }

    public function testHas()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->has('foo'));
        $this->assertFalse($this->storage->has('not_found'));
    }

    public function testIncrement()
    {
        $this->storage->set('num', 1);
        $this->assertEquals(2, $this->storage->increment('num', 1));
        $this->assertEquals(2, $this->storage->get('num'));
    }

    public function testDecrement()
    {
        $this->storage->set('num', 10);
        $this->assertEquals(7, $this->storage->decrement('num', 3));
        $this->assertEquals(7, $this->storage->get('num'));
    }

    public function testRemember()
    {
        $this->assertNull($this->storage->get('rem'));
        $value = $this->storage->remember('rem', 60, fn () => 'remembered');
        $this->assertEquals('remembered', $value);
        $this->assertEquals('remembered', $this->storage->get('rem'));
    }

    public function testMultiple()
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
