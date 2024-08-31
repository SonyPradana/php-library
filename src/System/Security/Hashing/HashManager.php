<?php

declare(strict_types=1);

namespace System\Security\Hashing;

class HashManager implements HashInterface
{
    private ?HashInterface $driver = null;

    public function setDriver(HashInterface $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    private function driver(?string $driver = null): HashInterface
    {
        return $driver ?? $this->driver ?? new DefaultHasher();
    }

    public function info(string $hashed_value): array
    {
        return $this->driver()->info($hashed_value);
    }

    public function make(string $value, array $options = []): string
    {
        return $this->driver()->make($value, $options);
    }

    public function verify(string $value, string $hashed_value, array $options = []): bool
    {
        return $this->driver()->verify($value, $hashed_value, $options);
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return $this->driver()->isValidAlgorithm($hash);
    }
}
