<?php

declare(strict_types=1);

namespace System\Text\Redis;

use PHPUnit\Framework\TestCase;
use System\Redis\Redis;

/**
 * @coversDefaultClass \System\Redis\Redis
 */
class RedisTest extends TestCase
{
    /**
     * The Redis connection.
     *
     * @var Redis
     */
    private $redis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redis = new Redis([
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 1,
        ]);
        $this->redis->flushdb();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->redis->flushdb();
        $this->redis = null;
    }

    /**
     * @test
     *
     * @covers ::set
     * @covers ::get
     *
     * @testdox Can set and get a value
     */
    public function itCanSetAndGetValues()
    {
        $this->assertTrue($this->redis->set('test_key', 'test_value'));
        $this->assertEquals('test_value', $this->redis->get('test_key'));
    }

    /**
     * @test
     *
     * @covers ::set
     * @covers ::get
     *
     * @testdox Can set a key with an expiry
     */
    public function itCanSetAKeyWithAnExpiry()
    {
        $this->assertTrue($this->redis->set('exp_key', 'exp_value', 1));
        $this->assertEquals('exp_value', $this->redis->get('exp_key'));
        sleep(2);
        $this->assertFalse($this->redis->get('exp_key'));
    }

    /**
     * @test
     *
     * @covers ::command
     *
     * @testdox Can run a raw command
     */
    public function itCanRunARawCommand()
    {
        $this->assertEquals('+PONG', $this->redis->command('ping'));
    }

    /**
     * @test
     *
     * @covers ::__call
     *
     * @testdox Throws an exception for a non-existent command
     */
    public function itThrowsAnExceptionForNonExistentCommand()
    {
        $this->expectException(\RedisException::class);

        $this->redis->notExistCommand();
    }

    /**
     * @test
     *
     * @covers ::del
     *
     * @testdox Can delete keys
     */
    public function itCanDeleteKeys()
    {
        $this->redis->set('test_key', 'test_value');
        $this->assertEquals(1, $this->redis->del('test_key'));
        $this->assertFalse($this->redis->get('test_key'));
    }

    /**
     * @test
     *
     * @covers ::exists
     *
     * @testdox Can check if a key exists
     */
    public function itCanCheckIfAKeyExists()
    {
        $this->redis->set('test_key', 'test_value');
        $this->assertEquals(1, $this->redis->exists('test_key'));
        $this->assertEquals(0, $this->redis->exists('non_existent_key'));
    }

    /**
     * @test
     *
     * @covers ::incr
     *
     * @testdox Can increment a value
     */
    public function itCanIncrementAValue()
    {
        $this->redis->set('counter', '1');
        $this->assertEquals(2, $this->redis->incr('counter'));
        $this->assertEquals('2', $this->redis->get('counter'));
    }

    /**
     * @test
     *
     * @covers ::decr
     *
     * @testdox Can decrement a value
     */
    public function itCanDecrementAValue()
    {
        $this->redis->set('counter', '2');
        $this->assertEquals(1, $this->redis->decr('counter'));
        $this->assertEquals('1', $this->redis->get('counter'));
    }

    /**
     * @test
     *
     * @covers ::keys
     *
     * @testdox Can get keys matching a pattern
     */
    public function itCanGetKeysMatchingAPattern()
    {
        $this->redis->set('key1', 'value1');
        $this->redis->set('key2', 'value2');
        $keys = $this->redis->keys('key*');
        $this->assertCount(2, $keys);
        $this->assertContains('key1', $keys);
        $this->assertContains('key2', $keys);
    }

    /**
     * @test
     *
     * @covers ::__call
     *
     * @testdox Can call redis commands magically
     */
    public function itCanCallRedisCommandsMagically()
    {
        $this->redis->hSet('hash', 'field', 'value');
        $this->assertEquals('value', $this->redis->hGet('hash', 'field'));
    }

    /**
     * @test
     *
     * @covers ::__call
     *
     * @testdox Can perform hash operations
     */
    public function itCanPerformHashOperations()
    {
        $this->redis->hSet('myhash', 'field1', 'value1');
        $this->redis->hSet('myhash', 'field2', 'value2');
        $this->assertEquals(2, $this->redis->hLen('myhash'));
        $this->assertEquals(1, $this->redis->hDel('myhash', 'field1'));
        $this->assertEquals(1, $this->redis->hLen('myhash'));
        $this->assertEquals('value2', $this->redis->hGet('myhash', 'field2'));
        $this->assertFalse($this->redis->hGet('myhash', 'field1'));
    }

    /**
     * @test
     *
     * @covers ::__call
     *
     * @testdox Can perform list operations
     */
    public function itCanPerformListOperations()
    {
        $this->redis->lPush('mylist', 'item1');
        $this->redis->lPush('mylist', 'item2');
        $this->assertEquals(2, $this->redis->lLen('mylist'));
        $this->assertEquals('item1', $this->redis->rPop('mylist'));
        $this->assertEquals('item2', $this->redis->rPop('mylist'));
        $this->assertFalse($this->redis->rPop('mylist'));
        $this->assertEquals(0, $this->redis->lLen('mylist'));
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers \System\Redis\RedisConnector::connect
     *
     * @testdox Can connect to a specific database
     */
    public function itCanConnectToASpecificDatabase()
    {
        $redis = new Redis([
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 0,
        ]);
        $redis->flushdb();

        $this->redis->set('key_db1', 'value_db1');
        $redis->set('key_db0', 'value_db0');

        $this->assertEquals('value_db1', $this->redis->get('key_db1'));
        $this->assertFalse($this->redis->get('key_db0'));

        $this->assertEquals('value_db0', $redis->get('key_db0'));
        $this->assertFalse($redis->get('key_db1'));

        $redis->flushdb();
    }
}
