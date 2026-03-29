<?php

declare(strict_types=1);

namespace System\Redis;

class RedisConnector
{
    /**
     * @param array{
     *     dsn?: string,
     *     host?: string,
     *     port?: int,
     *     timeout?: float,
     *     retry_interval?: int,
     *     read_timeout?: float,
     *     persistent?: bool,
     *     persistent_id?: string,
     *     password?: string,
     *     database?: int,
     *     unix_socket?: string,
     * } $config
     *
     * @return \Redis
     */
    public function connect(array $config): object
    {
        if (false === extension_loaded('redis')) {
            throw new \RuntimeException('The Redis extension is not loaded.');
        }

        if (isset($config['dsn'])) {
            $config = $this->parseDsn($config['dsn']) + $config;
            unset($config['dsn']);
        }

        $redis          = new \Redis();
        $timeout        = (float) ($config['timeout'] ?? 0.0);
        $retry_interval = (int) ($config['retry_interval'] ?? 0);
        $read_timeout   = (float) ($config['read_timeout'] ?? 0.0);
        $persistent     = (bool) ($config['persistent'] ?? false);
        $persistent_id  = (string) ($config['persistent_id'] ?? '');

        $host     = (string) ($config['unix_socket'] ?? $config['host'] ?? '127.0.0.1');
        $isSocket = isset($config['unix_socket']) || str_starts_with($host, '/');

        try {
            if ($isSocket) {
                $this->establishConnection(
                    $redis,
                    'connect',
                    [$host, 0, $timeout, $persistent_id, $retry_interval],
                    $persistent
                );
            } else {
                $this->establishConnection(
                    $redis,
                    'connect',
                    [
                        $host,
                        (int) ($config['port'] ?? 6379),
                        $timeout,
                        $persistent_id,
                        $retry_interval,
                    ],
                    $persistent
                );
            }

            if ($read_timeout > 0) {
                $redis->setOption(\Redis::OPT_READ_TIMEOUT, $read_timeout);
            }

            if (isset($config['password']) && $config['password'] !== '') {
                $redis->auth((string) $config['password']);
            }

            if (isset($config['database'])) {
                $redis->select((int) $config['database']);
            }
        } catch (\RedisException $e) {
            throw new \RedisException("Could not connect to Redis: {$e->getMessage()}", (int) $e->getCode(), $e);
        }

        return $redis;
    }

    /**
     * Parse DSN string menjadi config array.
     *
     * Supported formats:
     *   redis://127.0.0.1:6379
     *   redis://:password@127.0.0.1:6379
     *   redis://127.0.0.1:6379/2
     *   redis:///var/run/redis.sock
     *
     * @return array{
     *     host?: string,
     *     port?: int,
     *     password?: string,
     *     database?: int,
     *     unix_socket?: string,
     * }
     */
    protected function parseDsn(string $dsn): array
    {
        $parsed = parse_url($dsn);

        if (false === $parsed || ($parsed['scheme'] ?? '') !== 'redis') {
            throw new \InvalidArgumentException("Invalid Redis DSN: {$dsn}");
        }

        $config = [];

        if (isset($parsed['path']) && str_starts_with($parsed['path'], '/') && !isset($parsed['host'])) {
            $config['unix_socket'] = $parsed['path'];

            return $config;
        }

        if (isset($parsed['host'])) {
            $config['host'] = $parsed['host'];
        }

        if (isset($parsed['port'])) {
            $config['port'] = $parsed['port'];
        }

        if (isset($parsed['pass']) && $parsed['pass'] !== '') {
            $config['password'] = $parsed['pass'];
        }

        if (isset($parsed['path']) && $parsed['path'] !== '/') {
            $config['database'] = (int) ltrim($parsed['path'], '/');
        }

        return $config;
    }

    /**
     * Establish the connection to Redis.
     *
     * @param \Redis      $redis
     * @param list<mixed> $parameters
     */
    protected function establishConnection($redis, string $method, array $parameters, bool $persistent): void
    {
        $method = $persistent ? 'pconnect' : 'connect';

        $redis->{$method}(...$parameters);
    }
}
