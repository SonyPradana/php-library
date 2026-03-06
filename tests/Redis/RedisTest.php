<?php

declare(strict_types=1);

namespace System\Text\Redis;

use PHPUnit\Framework\TestCase;
use System\Redis\Redis;

/**
 * @coversDefaultClass \System\Redis\Redis
 *
 * @group redis
 */
class RedisTest extends TestCase
{
    /**
     * The Redis connection.
     *
     * @var Redis|null
     */
    private $redis;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded.');
        }

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
        if ($this->redis) {
            $this->redis->flushdb();
            $this->redis = null;
        }
    }

    /**
     * @test
     *
     * @covers ::set
     * @covers ::get
     *
     * @testdox Can set and get a value
     */
    public function it_can_set_and_get_values(): void
    {
        $this->assertTrue($this->redis->set('test_key', 'test_value'));
        $this->assertEquals('test_value', $this->redis->get('test_key'));
    }

    /**
     * @test
     *
     * @covers ::set
     *
     * @testdox Can set a value with expiry
     */
    public function it_can_set_a_value_with_expiry(): void
    {
        $this->assertTrue($this->redis->set('expire_key', 'expire_value', 1));
        $this->assertEquals('expire_value', $this->redis->get('expire_key'));

        // Wait for it to expire
        sleep(2);
        $this->assertFalse($this->redis->get('expire_key'));
    }

    /**
     * @test
     *
     * @covers ::get
     *
     * @testdox Returns false when a key does not exist
     */
    public function it_returns_false_when_a_key_does_not_exist(): void
    {
        $this->assertFalse($this->redis->get('non_existent_key'));
    }

    /**
     * @test
     *
     * @covers ::command
     *
     * @testdox Can run a raw command
     */
    public function it_can_run_a_raw_command(): void
    {
        $this->assertEquals('+PONG', $this->redis->command('ping'));
    }

    /**
     * @test
     *
     * @covers ::del
     *
     * @testdox Can delete keys
     */
    public function it_can_delete_keys(): void
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
    public function it_can_check_if_a_key_exists(): void
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
    public function it_can_increment_a_value(): void
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
    public function it_can_decrement_a_value(): void
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
    public function it_can_get_keys_matching_a_pattern(): void
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
    public function it_can_call_redis_commands_magically(): void
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
    public function it_can_perform_hash_operations(): void
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
    public function it_can_perform_list_operations(): void
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
    public function it_can_connect_to_a_specific_database(): void
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
