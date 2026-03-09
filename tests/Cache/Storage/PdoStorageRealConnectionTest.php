<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\PdoStorage;

/**
 * @group database
 *
 * @covers \System\Cache\Storage\PdoStorage
 */
class PdoStorageRealConnectionTest extends TestCase
{
    private \PDO $pdo;
    private PdoStorage $storage;
    private string $driver;

    protected function setUp(): void
    {
        $this->driver = $_ENV['DB_CONNECTION'] ?? 'mysql';
        $host         = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db           = $_ENV['DB_DATABASE'] ?? 'forge';
        $user         = $_ENV['DB_USERNAME'] ?? 'root';
        $pass         = $_ENV['DB_PASSWORD'] ?? '';
        $port         = $_ENV['DB_PORT'] ?? '3306';

        try {
            $dsn       = "{$this->driver}:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            $this->pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection failed: ' . $e->getMessage());
        }

        $this->createCacheTable();
        $this->storage = new PdoStorage($this->pdo, 'cache', 60);
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
            $this->dropCacheTable();
        }
    }

    private function createCacheTable(): void
    {
        $quote = match ($this->driver) {
            'mysql', 'mariadb' => '`',
            default            => '"',
        };

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS cache (
                {$quote}key{$quote} VARCHAR(255) PRIMARY KEY,
                {$quote}value{$quote} TEXT,
                {$quote}expiration{$quote} INT
            )
        ");
    }

    private function dropCacheTable(): void
    {
        $this->pdo->exec('DROP TABLE IF EXISTS cache');
    }

    /**
     * @test
     *
     * @testdox it can get and set cache on real connection
     *
     * @covers \System\Cache\Storage\PdoStorage::get
     * @covers \System\Cache\Storage\PdoStorage::set
     */
    public function itCanGetAndSetCacheOnRealConnection()
    {
        $this->assertTrue($this->storage->set('real_key', ['complex' => 'data', 'number' => 123]));
        $result = $this->storage->get('real_key');

        $this->assertIsArray($result);
        $this->assertEquals('data', $result['complex']);
        $this->assertEquals(123, $result['number']);
    }

    /**
     * @test
     *
     * @testdox it should handle cache expiration on real connection
     *
     * @covers \System\Cache\Storage\PdoStorage::get
     */
    public function itShouldHandleCacheExpirationOnRealConnection()
    {
        $this->storage->set('expired_soon', 'bye', 1);
        $this->assertEquals('bye', $this->storage->get('expired_soon'));

        // Wait for expiration
        sleep(2);

        $this->assertNull($this->storage->get('expired_soon'));
    }

    /**
     * @test
     *
     * @testdox it can increment cache on real connection
     *
     * @covers \System\Cache\Storage\PdoStorage::increment
     */
    public function itCanIncrementCacheOnRealConnection()
    {
        $this->storage->set('counter', 10, 10);
        $this->assertEquals(15, $this->storage->increment('counter', 5));
        $this->assertEquals(15, $this->storage->get('counter'));
    }

    /**
     * @test
     *
     * @testdox it can clear cache on real connection
     *
     * @covers \System\Cache\Storage\PdoStorage::clear
     */
    public function itCanClearCacheOnRealConnection()
    {
        $this->storage->set('a', 1);
        $this->storage->set('b', 2);
        $this->assertTrue($this->storage->clear());
        $this->assertFalse($this->storage->has('a'));
        $this->assertFalse($this->storage->has('b'));
    }
}
