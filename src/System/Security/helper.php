<?php

declare(strict_types=1);

namespace System\Security;

if (!function_exists('encrypt')) {
    function encrypt(
        string $plain_text,
        ?string $passphrase = null,
        string $algo = Algo::AES_256_CBC,
    ): string {
        return (new Crypt($passphrase, $algo))->encrypt($plain_text);
    }
}

if (!function_exists('decrypt')) {
    function decrypt(
        string $encrypted,
        ?string $passphrase = null,
        string $algo = Algo::AES_256_CBC,
    ): string {
        return (new Crypt($passphrase, $algo))->decrypt($encrypted);
    }
}
