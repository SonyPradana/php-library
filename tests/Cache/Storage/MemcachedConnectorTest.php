<?php

declare(strict_types=1);

namespace System\Test\Cache\Storage;

use PHPUnit\Framework\TestCase;
use System\Cache\Storage\MemcachedConnector;

/**
 * @group memcached
 */
class MemcachedConnectorTest extends TestCase
{
    /**
     * @test
     *
     * @testdox it can connect to memcached server
     *
     * @covers \System\Cache\Storage\MemcachedConnector::connect
     * @covers \System\Cache\Storage\MemcachedConnector::createMemcachedInstance
     */
    public function itCanConnectToMemcachedServer(): void
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped('Memcached extension is not installed.');
        }

        $connector = new MemcachedConnector();
        $memcached = $connector->connect(
            [['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100]],
            'test_persistent_id',
            [\Memcached::OPT_PREFIX_KEY => 'test_']
        );

        $this->assertInstanceOf('\Memcached', $memcached);

        $serverList = $memcached->getServerList();
        $this->assertCount(1, $serverList);
        $this->assertEquals('127.0.0.1', $serverList[0]['host']);
        $this->assertEquals(11211, $serverList[0]['port']);

        $this->assertEquals('test_', $memcached->getOption(\Memcached::OPT_PREFIX_KEY));
    }

    /**
     * @test
     *
     * @testdox it can connect to memcached via unix socket
     *
     * @covers \System\Cache\Storage\MemcachedConnector::connect
     */
    public function itCanConnectToMemcachedViaUnixSocket(): void
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped('Memcached extension is not installed.');
        }

        $connector = new MemcachedConnector();
        $memcached = $connector->connect(
            [['host' => '/var/run/memcached/memcached.sock', 'port' => 11211]]
        );

        $this->assertInstanceOf('\Memcached', $memcached);

        $serverList = $memcached->getServerList();
        $this->assertCount(1, $serverList);
        $this->assertEquals('/var/run/memcached/memcached.sock', $serverList[0]['host']);
        $this->assertEquals(0, $serverList[0]['port']);
    }
}
