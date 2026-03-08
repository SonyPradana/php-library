<?php

declare(strict_types=1);

namespace System\Cache\Storage;

class MemcachedConnector
{
    /**
     * Create a new Memcached connection.
     *
     * @param array<int, array{host: string, port: int, weight?: int}> $servers
     * @param array<int, mixed>                                        $options
     */
    public function connect(array $servers, ?string $persistent_id = null, array $options = []): \Memcached
    {
        $memcached = $this->createMemcachedInstance($persistent_id);

        if (count($memcached->getServerList()) === 0) {
            foreach ($servers as $server) {
                $host = $server['host'];
                $port = $server['port'];

                // If the host starts with a forward slash, it's a Unix socket.
                if (str_starts_with($host, '/')) {
                    $port = 0;
                }

                $memcached->addServer(
                    $host,
                    $port,
                    $server['weight'] ?? 0
                );
            }
        }

        if (count($options) > 0) {
            $memcached->setOptions($options);
        }

        return $memcached;
    }

    /**
     * Create a new Memcached instance.
     */
    protected function createMemcachedInstance(?string $persistent_id = null): \Memcached
    {
        return $persistent_id ? new \Memcached($persistent_id) : new \Memcached();
    }
}
