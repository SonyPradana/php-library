<?php

declare(strict_types=1);

namespace System\Security\Hashing;

class Argon2IdHasher extends ArgonHasher implements HashInterface
{
    public function make(string $value, array $options = []): string
    {
        $hash = @password_hash($value, PASSWORD_ARGON2ID, [
            'memory_cost' => $options['memory'] ?? $this->memory,
            'time_cost'   => $options['time'] ?? $this->time,
            'threads'     => $options['threads'] ?? $this->threads,
        ]);

        if (!is_string($hash)) {
            throw new \RuntimeException(PASSWORD_ARGON2ID . ' hashing not supported.');
        }

        return $hash;
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return 'argon2id' === $this->info($hash)['algoName'];
    }
}
