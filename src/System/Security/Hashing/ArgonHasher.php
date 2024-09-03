<?php

declare(strict_types=1);

namespace System\Security\Hashing;

class ArgonHasher extends DefaultHasher implements HashInterface
{
    protected int $memory = 1024;

    protected int $time = 2;

    protected int $threads = 2;

    public function setMemory(int $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function setThreads(int $threads): self
    {
        $this->threads = $threads;

        return $this;
    }

    public function make(string $value, array $options = []): string
    {
        $hash = @password_hash($value, PASSWORD_ARGON2I, [
            'memory_cost' => $options['memory'] ?? $this->memory,
            'time_cost'   => $options['time'] ?? $this->time,
            'threads'     => $options['threads'] ?? $this->threads,
        ]);

        if (!is_string($hash)) {
            throw new \RuntimeException(PASSWORD_ARGON2I . ' hashing not supported.');
        }

        return $hash;
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return 'argon2i' === $this->info($hash)['algoName'];
    }
}
