<?php

declare(strict_types=1);

namespace System\Security\Hashing;

interface HashInterface
{
    public function info(string $hash): array;

    public function verify(string $value, string $hashed_value, array $options = []): bool;

    public function make(string $value, array $options = []): string;

    public function isValidAlgorithm(string $hash): bool;
}
