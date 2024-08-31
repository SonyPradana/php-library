<?php

declare(strict_types=1);

namespace System\Security\Hashing;

class DefaultHasher implements HashInterface
{
    public function info(string $hash): array
    {
        return password_get_info($hash);
    }

    public function verify(string $value, string $hashed_value, array $options = []): bool
    {
        return password_verify($value, $hashed_value);
    }

    public function make(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return 'bcrypt' === $this->info($hash)['algoName'];
    }
}
