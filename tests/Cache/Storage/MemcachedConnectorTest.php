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
     * @covers \System\Cache\Storage\MemcachedConnector::serverExists
     */
    public function itCanConnectToMemcachedServer(): void
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped('Memcached extension is not installed.');
        }

        $connector = new MemcachedConnector();
        $memcached = $connector->connect(
            [['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100]],
            'test_persistent_id_1',
            [\Memcached::OPT_PREFIX_KEY => 'test_']
        );

        $this->assertInstanceOf('\Memcached', $memcached);

        $serverList = $memcached->getServerList();

        // Find our server in the list (in case of persistent connections from other runs)
        $found = false;
        foreach ($serverList as $server) {
            if ($server['host'] === '127.0.0.1' && $server['port'] === 11211) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
        $this->assertEquals('test_', $memcached->getOption(\Memcached::OPT_PREFIX_KEY));
    }

    /**
     * @test
     *
     * @testdox it can connect using DSN
     *
     * @covers \System\Cache\Storage\MemcachedConnector::connect
     * @covers \System\Cache\Storage\MemcachedConnector::parseDsn
     */
    public function itCanConnectUsingDsn(): void
    {
        if (!class_exists('\Memcached')) {
            $this->markTestSkipped('Memcached extension is not installed.');
        }

        $connector = new MemcachedConnector();
        $memcached = $connector->connect('memcached://127.0.0.1:11211?weight=50');

        $this->assertInstanceOf('\Memcached', $memcached);

        $serverList = $memcached->getServerList();
        $found      = false;
        foreach ($serverList as $server) {
            if ($server['host'] === '127.0.0.1' && $server['port'] === 11211) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }
}
