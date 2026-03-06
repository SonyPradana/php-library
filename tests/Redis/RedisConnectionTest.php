<?php

declare(strict_types=1);

namespace System\Text\Redis;

use PHPUnit\Framework\TestCase;
use System\Redis\Redis;

/**
 * @coversDefaultClass \System\Redis\Redis
 *
 * @covers \System\Redis\RedisConnector
 *
 * @group redis
 */
class RedisConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Redis extension not loaded.');
        }
    }

    /**
     * @test
     *
     * @testdox Can connect using persistent connection
     */
    public function itCanConnectUsingPersistentConnection(): void
    {
        $redis = new Redis([
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'database'   => 1,
            'persistent' => true,
        ]);

        $this->assertTrue($redis->set('persistent_key', 'value'));
        $this->assertEquals('value', $redis->get('persistent_key'));

        $redis->disconnect();
    }

    /**
     * @test
     *
     * @testdox Can set read timeout
     */
    public function itCanSetReadTimeout(): void
    {
        $redis = new Redis([
            'host'         => '127.0.0.1',
            'port'         => 6379,
            'database'     => 1,
            'read_timeout' => 2.5,
        ]);

        $this->assertEquals(2.5, $redis->client()->getOption(\Redis::OPT_READ_TIMEOUT));

        $redis->disconnect();
    }

    /**
     * @test
     *
     * @testdox Throws exception on connection failure
     */
    public function itThrowsExceptionOnConnectionFailure(): void
    {
        $this->expectException(\RedisException::class);
        $this->expectExceptionMessage('Could not connect to Redis');

        new Redis([
            'host'    => '127.0.0.1',
            'port'    => 9999, // Wrong port
            'timeout' => 0.1,
        ]);
    }
}
