<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use System\Cache\Storage\PdoStorage;
use System\Test\Database\TestDatabase;

class PdoStorageRealConnectionTest extends TestDatabase
{
    private PdoStorage $storage;

    protected function setUp(): void
    {
        $this->createConnection();
        $this->createCacheTable();

        $reflection = new \ReflectionClass($this->pdo);
        $property   = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $dbh = $property->getValue($this->pdo);

        $this->storage = new PdoStorage($dbh, 'cache', 60);
    }

    protected function tearDown(): void
    {
        $this->dropCacheTable();
    }

    private function createCacheTable(): void
    {
        $this->pdo->query('
            CREATE TABLE cache (
                key VARCHAR(255) PRIMARY KEY,
                value TEXT,
                expiration INT
            )
        ')->execute();
    }

    private function dropCacheTable(): void
    {
        $this->pdo->query('DROP TABLE IF EXISTS cache')->execute();
    }

    public function testRealConnectionGetAndSet()
    {
        $this->assertTrue($this->storage->set('real_key', ['complex' => 'data', 'number' => 123]));
        $result = $this->storage->get('real_key');

        $this->assertIsArray($result);
        $this->assertEquals('data', $result['complex']);
        $this->assertEquals(123, $result['number']);
    }

    public function testRealConnectionExpiration()
    {
        $this->storage->set('expired_soon', 'bye', 1);
        $this->assertEquals('bye', $this->storage->get('expired_soon'));

        // Wait for expiration
        sleep(2);

        $this->assertNull($this->storage->get('expired_soon'));
    }

    public function testRealConnectionIncrement()
    {
        $this->storage->set('counter', 10, 10);
        $this->assertEquals(15, $this->storage->increment('counter', 5));
        $this->assertEquals(15, $this->storage->get('counter'));
    }

    public function testRealConnectionClear()
    {
        $this->storage->set('a', 1);
        $this->storage->set('b', 2);
        $this->assertTrue($this->storage->clear());
        $this->assertFalse($this->storage->has('a'));
        $this->assertFalse($this->storage->has('b'));
    }
}
