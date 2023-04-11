<?php

declare(strict_types=1);

namespace System\Security;

class Crypt
{
    private string $cipher_algo;
    private string $iv;
    private string $hash;

    public function __construct(string $passphrase, string $cipher_algo)
    {
        $this->cipher_algo = $cipher_algo;
        $this->iv          = str_repeat((string) 0x0, 16);
        $this->hash        = $this->hash($passphrase);
    }

    public function hash(string $passphrase): string
    {
        return hash('sha256', $passphrase, true);
    }

    public function encrypt(string $plain_text, string $passphrase = null): string
    {
        $hash = $passphrase === null ? null : $this->hash($passphrase);

        return base64_encode(
            openssl_encrypt(
                $plain_text,
                $this->cipher_algo,
                $hash ?? $this->hash,
                OPENSSL_RAW_DATA,
                $this->iv
            )
        );
    }

    public function decrypt(string $encrypted, string $passphrase = null): string
    {
        $hash = $passphrase === null ? null : $this->hash($passphrase);

        return openssl_decrypt(
            base64_decode($encrypted),
            $this->cipher_algo,
            $hash ?? $this->hash,
            OPENSSL_RAW_DATA,
            $this->iv
        );
    }
}
