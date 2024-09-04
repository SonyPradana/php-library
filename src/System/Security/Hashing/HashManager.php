<?php

declare(strict_types=1);

namespace System\Security\Hashing;

class HashManager implements HashInterface
{
    /** @var array<string, HashInterface> */
    private $driver = [];

    private HashInterface $default_driver;

    public function __construct()
    {
        $this->setDefaultDriver(new DefaultHasher());
    }

    public function setDefaultDriver(HashInterface $driver): self
    {
        $this->default_driver = $driver;

        return $this;
    }

    public function setDriver(string $driver_name, HashInterface $driver): self
    {
        $this->driver[$driver_name] = $driver;

        return $this;
    }

    public function driver(?string $driver = null): HashInterface
    {
        if (array_key_exists($driver, $this->driver)) {
            return $this->driver[$driver];
        }

        return $this->default_driver;
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
