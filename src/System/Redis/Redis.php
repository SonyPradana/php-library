<?php

declare(strict_types=1);

namespace System\Redis;

use Redis as PhpRedis;

class Redis implements RedisInterface
{
    /**
     * The Redis connection.
     *
     * @var PhpRedis
     */
    protected $redis;

    /**
     * Create a new Redis instance.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $connector = new RedisConnector();
        $this->redis = $connector->connect($config);
    }

    /**
     * {@inheritdoc}
     */
    public function command(string $command, array $arguments = [])
    {
        return $this->redis->rawCommand($command, ...$arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function __call(string $method, array $arguments)
    {
        return $this->redis->{$method}(...$arguments);
    }
}
