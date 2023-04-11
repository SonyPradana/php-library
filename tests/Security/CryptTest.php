<?php

declare(strict_types=1);

namespace System\Test\Security;

use PHPUnit\Framework\TestCase;
use System\Security\Algo;
use System\Security\Crypt;

class CryptTest extends TestCase
{
    private Crypt $crypt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crypt = new Crypt('3sc3RLrpd17', Algo::AES_256_CBC);
    }

    /** @test */
    public function itCanEncryptCorrectly()
    {
        $encrypted = $this->crypt->encrypt('My secret message 1234');

        $this->assertEquals('LmkDyuTTToa9EaGdZmxU1CbKb/JEOvGoR9Fq4ZhTU9U=', $encrypted);
    }

    /** @test */
    public function itCanEncryptCorrectlyWithCostumePassphrase()
    {
        $encrypted = $this->crypt->encrypt('My secret message 1234', 'secret');

        $this->assertEquals('J15EYZ7FfI6Pkf7rembfMXTV9qM+RJBriA7uI8LxfLw=', $encrypted);
    }

    /** @test */
    public function itCanDecryptCorrectly()
    {
        $decrypted = $this->crypt->decrypt('LmkDyuTTToa9EaGdZmxU1CbKb/JEOvGoR9Fq4ZhTU9U=');

        $this->assertEquals('My secret message 1234', $decrypted);
    }

    /** @test */
    public function itCanDecryptCorrectlyWithCostumePassphrase()
    {
        $decrypted = $this->crypt->decrypt('J15EYZ7FfI6Pkf7rembfMXTV9qM+RJBriA7uI8LxfLw=', 'secret');

        $this->assertEquals('My secret message 1234', $decrypted);
    }
}
