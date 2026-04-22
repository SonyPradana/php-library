<?php

declare(strict_types=1);

namespace System\Text\Redis;

use PHPUnit\Framework\TestCase;
use System\Redis\RedisConnector;

class RedisConnectorTest extends TestCase
{
    /**
     * @test
     * @dataProvider dsnProvider
     */
    public function it_can_parse_redis_dsn(string $dsn, array $expected)
    {
        $connector = new RedisConnector();
        
        $parseDsn = function (string $dsn) {
            return $this->parseDsn($dsn);
        };

        $config = $parseDsn->call($connector, $dsn);

        $this->assertEquals($expected, $config);
    }

    public static function dsnProvider(): array
    {
        return [
            'basic' => [
                'redis://127.0.0.1:6379',
                [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                ],
            ],
            'with password' => [
                'redis://:password@127.0.0.1:6379',
                [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'password' => 'password',
                ],
            ],
            'with database' => [
                'redis://127.0.0.1:6379/2',
                [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 2,
                ],
            ],
            'with password and database' => [
                'redis://:secret@localhost:6379/1',
                [
                    'host' => 'localhost',
                    'port' => 6379,
                    'password' => 'secret',
                    'database' => 1,
                ],
            ],
            'unix socket' => [
                'redis:///var/run/redis.sock',
                [
                    'unix_socket' => '/var/run/redis.sock',
                ],
            ],
            'unix socket single slash' => [
                'redis:/var/run/redis.sock',
                [
                    'unix_socket' => '/var/run/redis.sock',
                ],
            ],
            'host only' => [
                'redis://localhost',
                [
                    'host' => 'localhost',
                ],
            ],
            'with trailing slash' => [
                'redis://localhost:6379/',
                [
                    'host' => 'localhost',
                    'port' => 6379,
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_dsn_scheme()
    {
        $connector = new RedisConnector();
        
        $parseDsn = function (string $dsn) {
            return $this->parseDsn($dsn);
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Redis DSN: http://127.0.0.1');

        $parseDsn->call($connector, 'http://127.0.0.1');
    }

    /**
     * @test
     */
    public function it_throws_exception_for_malformed_dsn()
    {
        $connector = new RedisConnector();
        
        $parseDsn = function (string $dsn) {
            return $this->parseDsn($dsn);
        };

        $this->expectException(\InvalidArgumentException::class);
        
        $parseDsn->call($connector, 'redis://:6379:invalid');
    }

    /**
     * @test
     */
    public function it_can_connect_with_dsn_config()
    {
        $connector = new class extends RedisConnector {
            public $establishConnectionCalled = false;
            public $parameters = [];
            public $method = '';
            protected function establishConnection($redis, string $method, array $parameters, bool $persistent): void
            {
                $this->establishConnectionCalled = true;
                $this->method = $method;
                $this->parameters = $parameters;
            }
        };

        $config = [
            'dsn' => 'redis://:secret@localhost:6379/2',
            'timeout' => 2.5,
        ];

        // We use a real \Redis object but we don't connect it.
        // Some methods might throw if not connected, but select/auth usually just return false or throw \RedisException
        // if the extension is strictly checking connection state.
        try {
            $redis = $connector->connect($config);
            $this->assertInstanceOf(\Redis::class, $redis);
        } catch (\RedisException $e) {
            // If it throws RedisException because of select/auth on non-connected redis,
            // we at least verified it reached that point.
            $this->assertStringContainsString('Could not connect to Redis', $e->getMessage());
        }

        $this->assertTrue($connector->establishConnectionCalled);
        $this->assertEquals('localhost', $connector->parameters[0]);
        $this->assertEquals(6379, $connector->parameters[1]);
        $this->assertEquals(2.5, $connector->parameters[2]);
    }

    /**
     * @test
     */
    public function it_can_connect_with_array_config()
    {
        $connector = new class extends RedisConnector {
            public $establishConnectionCalled = false;
            public $parameters = [];
            protected function establishConnection($redis, string $method, array $parameters, bool $persistent): void
            {
                $this->establishConnectionCalled = true;
                $this->parameters = $parameters;
            }
        };

        $config = [
            'host' => '127.0.0.1',
            'port' => 6380,
            'timeout' => 1.0,
        ];

        try {
            $redis = $connector->connect($config);
            $this->assertInstanceOf(\Redis::class, $redis);
        } catch (\RedisException $e) {
             $this->assertStringContainsString('Could not connect to Redis', $e->getMessage());
        }

        $this->assertTrue($connector->establishConnectionCalled);
        $this->assertEquals('127.0.0.1', $connector->parameters[0]);
        $this->assertEquals(6380, $connector->parameters[1]);
    }

    /**
     * @test
     */
    public function it_can_connect_via_unix_socket()
    {
        $connector = new class extends RedisConnector {
            public $establishConnectionCalled = false;
            public $parameters = [];
            protected function establishConnection($redis, string $method, array $parameters, bool $persistent): void
            {
                $this->establishConnectionCalled = true;
                $this->parameters = $parameters;
            }
        };

        $config = [
            'unix_socket' => '/tmp/redis.sock',
        ];

        try {
            $redis = $connector->connect($config);
            $this->assertInstanceOf(\Redis::class, $redis);
        } catch (\RedisException $e) {
             $this->assertStringContainsString('Could not connect to Redis', $e->getMessage());
        }

        $this->assertTrue($connector->establishConnectionCalled);
        $this->assertEquals('/tmp/redis.sock', $connector->parameters[0]);
        $this->assertEquals(0, $connector->parameters[1]);
    }

    /**
     * @test
     */
    public function it_can_handle_persistent_connection()
    {
        $connector = new class extends RedisConnector {
            public $method = '';
            public $persistent = false;
            public $parameters = [];
            protected function establishConnection($redis, string $method, array $parameters, bool $persistent): void
            {
                $this->method = $method;
                $this->persistent = $persistent;
                $this->parameters = $parameters;
            }
        };

        $config = [
            'host' => '127.0.0.1',
            'persistent' => true,
            'persistent_id' => 'my-id',
        ];

        $connector->connect($config);

        $this->assertTrue($connector->persistent);
        $this->assertEquals('my-id', $connector->parameters[3]);
    }

    /**
     * @test
     */
    public function it_can_set_read_timeout()
    {
        // This test is a bit tricky as it calls setOption on real Redis object.
        // But we can check if it runs without crashing.
        $connector = new class extends RedisConnector {
            protected function establishConnection($redis, string $method, array $parameters, bool $persistent): void
            {
                // Don't connect
            }
        };

        $config = [
            'host' => '127.0.0.1',
            'read_timeout' => 5.0,
        ];

        try {
            $redis = $connector->connect($config);
            $this->assertInstanceOf(\Redis::class, $redis);
        } catch (\RedisException $e) {
             $this->assertStringContainsString('Could not connect to Redis', $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_calls_establish_connection_with_correct_method()
    {
        $connector = new RedisConnector();
        
        $establishConnection = function ($redis, $method, $parameters, $persistent) {
            $this->establishConnection($redis, $method, $parameters, $persistent);
        };

        $redis = $this->getMockBuilder('Redis')
            ->onlyMethods(['connect', 'pconnect'])
            ->getMock();

        $redis->expects($this->once())
            ->method('connect')
            ->with('127.0.0.1', 6379);

        $establishConnection->call($connector, $redis, 'any', ['127.0.0.1', 6379], false);

        $redis2 = $this->getMockBuilder('Redis')
            ->onlyMethods(['connect', 'pconnect'])
            ->getMock();

        $redis2->expects($this->once())
            ->method('pconnect')
            ->with('127.0.0.1', 6379);

        $establishConnection->call($connector, $redis2, 'any', ['127.0.0.1', 6379], true);
    }
}
