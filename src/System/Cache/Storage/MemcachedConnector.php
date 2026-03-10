<?php

declare(strict_types=1);

namespace System\Cache\Storage;

class MemcachedConnector
{
    /**
     * Create a new Memcached connection from DSN or array of servers.
     *
     * @param array<int, array{host: string, port?: int, weight?: int}>|string $servers
     * @param array<int, mixed>                                                $options
     *
     * @return \Memcached
     */
    public function connect(array|string $servers, ?string $persistent_id = null, array $options = []): object
    {
        $memcached = $this->createMemcachedInstance($persistent_id);

        if (is_string($servers)) {
            $servers = $this->parseDsn($servers);
        }

        $currentServers = $memcached->getServerList();

        foreach ($servers as $server) {
            $host = $server['host'];
            $port = str_starts_with($host, '/') ? 0 : ($server['port'] ?? 11211);

            if (false === $this->serverExists($host, $port, $currentServers)) {
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
     * Parse Dsn string to servers array.
     * Format: memcached://host:port?weight=100 or memcached:///path/to/socket.
     *
     * @return array<int, array{host: string, port: int, weight: int}>
     */
    public function parseDsn(string $dsn): array
    {
        if (false === str_starts_with($dsn, 'memcached://')) {
            throw new \InvalidArgumentException('Invalid Memcached DSN format.');
        }

        $url = parse_url($dsn);
        if ($url === false) {
            throw new \InvalidArgumentException('Could not parse Memcached DSN.');
        }

        $host = $url['host'] ?? '';
        $port = $url['port'] ?? 11211;
        $path = $url['path'] ?? '';

        // Handle Unix Socket (memcached:///path/to/socket)
        if ($host === '' && $path !== '') {
            $host = $path;
            $port = 0;
        }

        $weight = 0;
        if (isset($url['query'])) {
            parse_str($url['query'], $query);
            $weight = (int) ($query['weight'] ?? 0);
        }

        return [[
            'host'   => $host,
            'port'   => (int) $port,
            'weight' => $weight,
        ]];
    }

    /**
     * Create a new Memcached instance.
     *
     * @return \Memcached
     */
    protected function createMemcachedInstance(?string $persistent_id = null): object
    {
        /** @var class-string<\Memcached> $class */
        $class = '\Memcached';

        return $persistent_id ? new $class($persistent_id) : new $class();
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
