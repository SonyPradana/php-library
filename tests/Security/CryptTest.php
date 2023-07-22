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
    public function itCanEncryptDecryptCorrectly()
    {
        $plan_text = 'My secret message 1234';
        $encrypted = $this->crypt->encrypt($plan_text);
        $decrypted = $this->crypt->decrypt($encrypted);

        $this->assertEquals($plan_text, $decrypted);
    }

    /** @test */
    public function itCanEncryptCorrectlyWithCostumePassphrase()
    {
        $plan_text = 'My secret message 1234';
        $encrypted = $this->crypt->encrypt($plan_text, 'secret');
        $decrypted = $this->crypt->decrypt($encrypted, 'secret');

        $this->assertEquals('My secret message 1234', $decrypted);
    }
}
