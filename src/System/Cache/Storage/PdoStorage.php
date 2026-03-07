<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;

class PdoStorage implements CacheInterface
{
    public function __construct(
        private \PDO $pdo,
        private string $table = 'cache',
        private int $defaultTTL = 3_600,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $stmt = $this->pdo->prepare("SELECT value, expiration FROM {$this->table} WHERE key = :key");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (false === $row) {
            return $default;
        }

        if (time() >= (int) $row['expiration']) {
            $this->delete($key);

            return $default;
        }

        return unserialize($row['value']);
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $expiration = $this->calculateExpirationTimestamp($ttl);
        $serialized = serialize($value);

        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE key = :key");
        $stmt->execute(['key' => $key]);

        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (key, value, expiration) VALUES (:key, :value, :expiration)");

        return $stmt->execute([
            'key'        => $key,
            'value'      => $serialized,
            'expiration' => $expiration,
        ]);
    }

    public function delete(string $key): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE key = :key");

        return $stmt->execute(['key' => $key]);
    }

    public function clear(): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table}");

        return $stmt->execute();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (false === $this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (false === $this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    public function has(string $key): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->table} WHERE key = :key AND expiration > :now");
        $stmt->execute([
            'key' => $key,
            'now' => time(),
        ]);

        return false !== $stmt->fetchColumn();
    }

    public function increment(string $key, int $value): int
    {
        $stmt = $this->pdo->prepare("SELECT value, expiration FROM {$this->table} WHERE key = :key");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (false === $row || time() >= (int) $row['expiration']) {
            $this->set($key, $value);

            return $value;
        }

        $current    = unserialize($row['value']);
        $expiration = (int) $row['expiration'];

        if (false === is_int($current)) {
            throw new \InvalidArgumentException('Value increment must be integer.');
        }

        $new = $current + $value;
        $ttl = $expiration - time();

        $this->set($key, $new, max(0, $ttl));

        return $new;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
    }

    public function remember(string $key, int|\DateInterval|null $ttl, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    private function calculateExpirationTimestamp(int|\DateInterval|\DateTimeInterface|null $ttl): int
    {
        if ($ttl instanceof \DateInterval) {
            return (new \DateTimeImmutable())->add($ttl)->getTimestamp();
        }

        if ($ttl instanceof \DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        $ttl ??= $this->defaultTTL;

        return time() + $ttl;
    }
}
