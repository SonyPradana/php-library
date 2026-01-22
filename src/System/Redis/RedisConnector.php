<?php

declare(strict_types=1);

namespace System\Redis;

use Redis as PhpRedis;
use RuntimeException;

class RedisConnector
{
    /**
     * Create a new Redis connection.
     *
     * @param array<string, mixed> $config
     *
     * @return PhpRedis
     */
    public function connect(array $config): PhpRedis
    {
        if (!class_exists('Redis')) {
            throw new RuntimeException('Redis extension is not installed.');
        }

        $redis = new PhpRedis();

        $redis->connect(
            $config['host'] ?? '12-7.0.0.1',
            $config['port'] ?? 6379,
            $config['timeout'] ?? 0.0,
            $config['reserved'] ?? null,
            $config['retry_interval'] ?? 0,
            $config['read_timeout'] ?? 0.0
        );

        if (isset($config['password'])) {
            $redis->auth($config['password']);
        }

        if (isset($config['database'])) {
            $redis->select($config['database']);
        }

        return $redis;
    }
}
