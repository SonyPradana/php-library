<?php

declare(strict_types=1);

namespace System\Test\Security\Hashing;

use PHPUnit\Framework\TestCase;
use System\Security\Hashing\BcryptHasher;
use System\Security\Hashing\HashManager;

class HasherMangerTest extends TestCase
{
    /** @test */
    public function itCanHashDefaultHasher()
    {
        $hasher = new HashManager();
        $hasher->setDriver(new BcryptHasher());
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }
}
