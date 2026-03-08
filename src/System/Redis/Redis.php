<?php

declare(strict_types=1);

namespace System\Redis;

use Redis as PhpRedis;

/**
 * @method bool         flushdb()
 * @method int|false    hSet(string $key, string $hashKey, string $value)
 * @method string|false hGet(string $key, string $hashKey)
 * @method int|false    hLen(string $key)
 * @method int|false    hDel(string $key, string ...$hashKeys)
 * @method int|false    lPush(string $key, mixed ...$values)
 * @method mixed        rPop(string $key)
 * @method int|false    lLen(string $key)
 */
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
     */
    public function __construct(array $config)
    {
        if (false === extension_loaded('redis')) {
            throw new \RuntimeException('The Redis extension is not loaded.');
        }

        $connector   = new RedisConnector();
        $this->redis = $connector->connect($config);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, ?int $timeout = null): bool
    {
        if (null !== $timeout) {
            return $this->redis->setex($key, $timeout, $value);
        }

        return $this->redis->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function del(string|array $keys): int
    {
        return $this->redis->del($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function incr(string $key): int|false
    {
        return $this->redis->incr($key);
    }

    /**
     * {@inheritdoc}
     */
    public function decr(string $key): int|false
    {
        return $this->redis->decr($key);
    }

    /**
     * {@inheritdoc}
     */
    public function keys(string $pattern): array
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
    public function disconnect(): void
    {
        $this->redis->close();
    }

    /**
     * {@inheritdoc}
     */
    public function command(string $command, array $arguments = []): mixed
    {
        return $this->redis->rawCommand($command, ...$arguments);
    }

    /**
     * Call redis raw command from magic method.
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->redis->{$method}(...$arguments);
    }

    /**
     * Flushes all the databases.
     */
    public function flushdb(): bool
    {
        return $this->redis->flushdb();
    }
}
