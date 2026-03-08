<?php

declare(strict_types=1);

namespace System\Cache\Storage;

class MemcachedConnector
{
    /**
     * Create a new Memcached connection.
     *
     * @param array<int, array{host: string, port?: int, weight?: int}> $servers
     * @param array<int, mixed>                                         $options
     */
    public function connect(array $servers, ?string $persistent_id = null, array $options = []): \Memcached
    {
        $memcached = $this->createMemcachedInstance($persistent_id);

        $currentServers = $memcached->getServerList();

        foreach ($servers as $server) {
            $host = $server['host'];
            $port = str_starts_with($host, '/') ? 0 : ($server['port'] ?? 11211);

            if (!$this->serverExists($host, $port, $currentServers)) {
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

    /**
     * @param array<int, array{host: string, port: int}> $currentServers
     */
    private function serverExists(string $host, int $port, array $currentServers): bool
    {
        foreach ($currentServers as $server) {
            if ($server['host'] === $host && $server['port'] === $port) {
                return true;
            }
        }

        return false;
    }
}
