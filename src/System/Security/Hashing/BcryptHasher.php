<?php

declare(strict_types=1);

namespace System\Security\Hashing;

class BcryptHasher extends DefaultHasher implements HashInterface
{
    protected int $rounds = 12;

    public function setRounds(int $rounds): self
    {
        $this->rounds = $rounds;

        return $this;
    }

    public function make(string $value, array $options = []): string
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $options['rounds'] ?? $this->rounds,
        ]);

        return $hash;
    }

    public function isValidAlgorithm(string $hash): bool
    {
        return 'bcrypt' === $this->info($hash)['algoName'];
    }
}
