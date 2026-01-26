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
        $connector   = new RedisConnector();
        $this->redis = $connector->connect($config);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, $timeout = null)
    {
        return $this->redis->set($key, $value, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function del($keys)
    {
        return $this->redis->del($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $key)
    {
        return $this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function incr(string $key)
    {
        return $this->redis->incr($key);
    }

    /**
     * {@inheritdoc}
     */
    public function decr(string $key)
    {
        return $this->redis->decr($key);
    }

    /**
     * {@inheritdoc}
     */
    public function keys(string $pattern)
    {
        return $this->redis->keys($pattern);
    }

    /**
     * {@inheritdoc}
     */
    public function client(): object
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return 'PHPRedis';
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
