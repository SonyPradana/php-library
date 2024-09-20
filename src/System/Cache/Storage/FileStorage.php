<?php

declare(strict_types=1);

namespace System\Cache\Storage;

use System\Cache\CacheInterface;

class FileStorage implements CacheInterface
{
    public function __construct(
        private string $path,
        private int $defaultTTL = 3_600,
    ) {
        if (false === is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * Get info of storage.
     *
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    public function getInfo(string $key): array
    {
        $filePath = $this->makePath($key);

        if (false === file_exists($filePath)) {
            return [];
        }

        $data = file_get_contents($filePath);

        if (false === $data) {
            return [];
        }

        return unserialize($data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $filePath = $this->makePath($key);

        if (false === file_exists($filePath)) {
            return $default;
        }

        $data = file_get_contents($filePath);

        if ($data === false) {
            return $default;
        }

        $cacheData = unserialize($data);

        if (time() >= $cacheData['timestamp']) {
            $this->delete($key);

            return $default;
        }

        return $cacheData['value'];
    }

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        $filePath  = $this->makePath($key);
        $directory = dirname($filePath);

        if (false === is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $cacheData = [
            'value'     => $value,
            'timestamp' => $this->calculateExpirationTimestamp($ttl),
            'mtime'     => $this->createMtime(),
        ];

        $serializedData = serialize($cacheData);

        return file_put_contents($filePath, $serializedData, LOCK_EX) !== false;
    }

    public function delete(string $key): bool
    {
        $filePath = $this->makePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    public function clear(): bool
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $filePath = $fileinfo->getRealPath();

            if (basename($filePath) === '.gitignore') {
                continue;
            }

            $action = $fileinfo->isDir() ? 'rmdir' : 'unlink';
            $action($filePath);
        }

        return true;
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
        $state = null;

        foreach ($values as $key => $value) {
            $result = $this->set($key, $value, $ttl);
            $state  = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);
            $state  = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    public function has(string $key): bool
    {
        return file_exists($this->makePath($key));
    }

    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, 0);

            return $value;
        }

        $info = $this->getInfo($key);

        $ori = $info['value'] ?? 0;
        $ttl = $info['timestamp'] ?? 0;

        if (false === is_int($ori)) {
            throw new \InvalidArgumentException('Value incremnet must be interger.');
        }

        $result = (int) ($ori + $value);

        $this->set($key, $result, $ttl);

        return $result;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
    }

    public function remember(string $key, int|\DateInterval|null $ttl = null, \Closure $callback): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Generate the full file path for the given cache key.
     */
    protected function makePath(string $key): string
    {
        $hash  = sha1($key);
        $parts = array_slice(str_split($hash, 2), 0, 2);

        return $this->path . '/' . implode('/', $parts) . '/' . $hash;
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

    /**
     * Calculate the microtime based on the current time and microtime.
     */
    private function createMtime(): float
    {
        $currentTime = time();
        $microtime   = microtime(true);

        $fractionalPart = $microtime - $currentTime;

        if ($fractionalPart >= 1) {
            $currentTime += (int) $fractionalPart;
            $fractionalPart -= (int) $fractionalPart;
        }

        $mtime = $currentTime + $fractionalPart;

        return round($mtime, 3);
    }
}
