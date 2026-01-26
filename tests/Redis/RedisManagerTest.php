<?php

declare(strict_types=1);

namespace System\Text\Redis;

use PHPUnit\Framework\TestCase;
use System\Redis\Redis;
use System\Redis\RedisInterface;
use System\Redis\RedisManager;

/**
 * @covers \System\Redis\RedisManager
 */
class RedisManagerTest extends TestCase
{
    private function createRedisDriver(): Redis
    {
        // Assumes redis is running on localhost:6379
        // and using database 1 for testing
        return new Redis([
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        $redis = $this->createRedisDriver();
        $redis->flushdb();
        parent::tearDown();
    }

    /** @test */
    public function itCanSetAndGetDefaultDriver(): void
    {
        $manager = new RedisManager();
        $driver  = $this->createRedisDriver();

        $manager->setDefaultDriver($driver);

        $this->assertInstanceOf(RedisInterface::class, $manager->driver());
        $this->assertSame($driver, $manager->driver());

        $manager->set('manager-key', 'manager-value');

        $this->assertEquals('manager-value', $driver->get('manager-key'));
        $this->assertEquals('manager-value', $manager->get('manager-key'));
    }

    /** @test */
    public function itCanSetAndGetNamedDrivers(): void
    {
        $manager = new RedisManager();

        // setup default
        $default_driver = $this->createRedisDriver();
        $manager->setDefaultDriver($default_driver);
        $manager->set('default-key', 'default-value');

        // setup named driver
        $named_driver = new Redis([
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => 2, // use different database
        ]);
        $manager->setDriver('second', $named_driver);

        $this->assertInstanceOf(RedisInterface::class, $manager->driver('second'));
        $this->assertSame($named_driver, $manager->driver('second'));

        // interact with named driver
        $manager->driver('second')->set('named-key', 'named-value');
        $this->assertEquals('named-value', $manager->driver('second')->get('named-key'));

        // ensure default driver is not affected
        $this->assertEquals('default-value', $manager->get('default-key'));
        $this->assertFalse($manager->get('named-key')); // key should not exist in default driver

        // ensure named driver is not affected by default
        $this->assertFalse($manager->driver('second')->get('default-key')); // key should not exist in named driver
    }

    /** @test */
    public function itCanUseClosureAsDriver(): void
    {
        $manager = new RedisManager();
        $manager->setDriver('lazy', function () {
            return $this->createRedisDriver();
        });

        $this->assertInstanceOf(RedisInterface::class, $manager->driver('lazy'));

        $manager->driver('lazy')->set('lazy-key', 'lazy-value');
        $this->assertEquals('lazy-value', $manager->driver('lazy')->get('lazy-key'));
    }

    /** @test */
    public function itCanConnectViaUnixSocket(): void
    {
        $socket_path = '/var/run/redis/redis-server.sock';

        if (false === file_exists($socket_path)) {
            $this->markTestSkipped(
                "Redis socket not found at {$socket_path}."
            );
        }

        $manager = new RedisManager();
        $driver  = new Redis([
            'unix_socket' => $socket_path,
            'database'    => 1,
        ]);
        $manager->setDefaultDriver($driver);

        $this->assertTrue($manager->set('socket-key', 'socket-value'));
        $this->assertEquals('socket-value', $manager->get('socket-key'));
    }
}
