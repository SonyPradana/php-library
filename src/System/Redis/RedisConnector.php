<?php

declare(strict_types=1);

namespace System\Redis;

class RedisConnector
{
    /**
     * @param array{
     *     host?: string,
     *     port?: int,
     *     timeout?: int,
     *     password?: string,
     *     database?: int,
     *     unix_socket?: string,
     * } $config
     */
    public function connect(array $config): \Redis
    {
        $redis = new \Redis();

        if (isset($config['unix_socket'])) {
            $redis->connect((string) $config['unix_socket']);
        } else {
            $redis->connect(
                (string) ($config['host'] ?? '127.0.0.1'),
                (int) ($config['port'] ?? 6379),
                (int) ($config['timeout'] ?? 0)
            );
        }

        if (isset($config['password'])) {
            $redis->auth((string) $config['password']);
        }

        if (isset($config['database'])) {
            $redis->select((int) $config['database']);
        }

        return $redis;
    }
}
