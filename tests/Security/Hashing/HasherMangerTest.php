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
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /** @test */
    public function itCanUsingDriver()
    {
        $hasher = new HashManager();
        $hasher->setDriver('bcrypt', new BcryptHasher());
        $hash   = $hasher->driver('bcrypt')->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->driver('bcrypt')->verify('password', $hash));
        $this->assertTrue($hasher->driver('bcrypt')->isValidAlgorithm($hash));
    }
}
