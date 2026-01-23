<?php

declare(strict_types=1);

namespace System\Text\Redis;

use PHPUnit\Framework\TestCase;
use System\Redis\Redis;

class RedisTest extends TestCase
{
    /**
     * @var Redis
     */
    private $redis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redis = new Redis([
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 1,
        ]);
        $this->redis->flushdb();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->redis->flushdb();
    }

    public function testCanSetAndGet()
    {
        $this->assertTrue($this->redis->set('test_key', 'test_value'));
        $this->assertEquals('test_value', $this->redis->get('test_key'));
    }

    public function testSetWithExpiry()
    {
        $this->assertTrue($this->redis->set('exp_key', 'exp_value', 1)); // Set with 1-second expiry
        $this->assertEquals('exp_value', $this->redis->get('exp_key')); // Should be present immediately
        sleep(2); // Wait for key to expire
        $this->assertFalse($this->redis->get('exp_key')); // Should be expired
    }

    public function testCanUseCommand()
    {
        $this->assertEquals('+PONG', $this->redis->command('ping'));
    }

    public function testCallNotExistCommand()
    {
        $this->expectException(\RedisException::class);

        $this->redis->notExistCommand();
    }

    public function testCanDelete()
    {
        $this->redis->set('test_key', 'test_value');
        $this->assertEquals(1, $this->redis->del('test_key'));
        $this->assertFalse($this->redis->get('test_key'));
    }

    public function testExists()
    {
        $this->redis->set('test_key', 'test_value');
        $this->assertEquals(1, $this->redis->exists('test_key'));
        $this->assertEquals(0, $this->redis->exists('non_existent_key'));
    }

    public function testIncr()
    {
        $this->redis->set('counter', '1');
        $this->assertEquals(2, $this->redis->incr('counter'));
        $this->assertEquals('2', $this->redis->get('counter'));
    }

    public function testDecr()
    {
        $this->redis->set('counter', '2');
        $this->assertEquals(1, $this->redis->decr('counter'));
        $this->assertEquals('1', $this->redis->get('counter'));
    }

    public function testKeys()
    {
        $this->redis->set('key1', 'value1');
        $this->redis->set('key2', 'value2');
        $keys = $this->redis->keys('key*');
        $this->assertCount(2, $keys);
        $this->assertContains('key1', $keys);
        $this->assertContains('key2', $keys);
    }

    public function testCall()
    {
        $this->redis->hSet('hash', 'field', 'value');
        $this->assertEquals('value', $this->redis->hGet('hash', 'field'));
    }

    public function testHashOperations()
    {
        $this->redis->hSet('myhash', 'field1', 'value1');
        $this->redis->hSet('myhash', 'field2', 'value2');
        $this->assertEquals(2, $this->redis->hLen('myhash'));
        $this->assertEquals(1, $this->redis->hDel('myhash', 'field1')); // Returns number of fields deleted
        $this->assertEquals(1, $this->redis->hLen('myhash'));
        $this->assertEquals('value2', $this->redis->hGet('myhash', 'field2'));
        $this->assertFalse($this->redis->hGet('myhash', 'field1'));
    }

    public function testListOperations()
    {
        $this->redis->lPush('mylist', 'item1');
        $this->redis->lPush('mylist', 'item2');
        $this->assertEquals(2, $this->redis->lLen('mylist'));
        $this->assertEquals('item1', $this->redis->rPop('mylist'));
        $this->assertEquals('item2', $this->redis->rPop('mylist'));
        $this->assertFalse($this->redis->rPop('mylist')); // List should be empty
        $this->assertEquals(0, $this->redis->lLen('mylist'));
    }

    public function testConnectWithDatabase()
    {
        $redis = new Redis([
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0, // Connect to database 0
        ]);
        $redis->flushdb(); // Clear database 0

        $this->redis->set('key_db1', 'value_db1'); // Still in database 1
        $redis->set('key_db0', 'value_db0'); // In database 0

        $this->assertEquals('value_db1', $this->redis->get('key_db1'));
        $this->assertFalse($this->redis->get('key_db0'));

        $this->assertEquals('value_db0', $redis->get('key_db0'));
        $this->assertFalse($redis->get('key_db1'));

        $redis->flushdb(); // Clear database 0
    }
}
