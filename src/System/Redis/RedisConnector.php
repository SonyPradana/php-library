<?php

declare(strict_types=1);

namespace System\Redis;

class RedisConnector
{
    /**
     * @param array{
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

        $redis = new \Redis();

        $timeout        = (float) ($config['timeout'] ?? 0.0);
        $retry_interval = (int) ($config['retry_interval'] ?? 0);
        $read_timeout   = (float) ($config['read_timeout'] ?? 0.0);
        $persistent     = (bool) ($config['persistent'] ?? false);
        $persistent_id  = (string) ($config['persistent_id'] ?? '');

        try {
            if (isset($config['unix_socket'])) {
                $this->establishConnection(
                    $redis,
                    'connect',
                    [(string) $config['unix_socket'], 0, $timeout, $persistent_id, $retry_interval],
                    $persistent
                );
            } else {
                $this->establishConnection(
                    $redis,
                    'connect',
                    [
                        (string) ($config['host'] ?? '127.0.0.1'),
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
