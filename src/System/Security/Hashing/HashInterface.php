<?php

declare(strict_types=1);

namespace System\Security\Hashing;

interface HashInterface
{
    /**
     * Get information about hash.
     *
     * @return array<string, int|string|bool>
     */
    public function info(string $hash): array;

    /**
     * Verify hash and hashed.
     *
     * @param array<string, int|string|bool> $options
     */
    public function verify(string $value, string $hashed_value, array $options = []): bool;

    /**
     * Hash given string.
     *
     * @param array<string, int|string|bool> $options
     *
     * @throws \RuntimeException
     */
    public function make(string $value, array $options = []): string;

    public function isValidAlgorithm(string $hash): bool;
}
