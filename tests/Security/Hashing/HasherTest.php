<?php

declare(strict_types=1);

namespace System\Test\Security\Hashing;

use PHPUnit\Framework\TestCase;
use System\Security\Hashing\Argon2IdHasher;
use System\Security\Hashing\ArgonHasher;
use System\Security\Hashing\BcryptHasher;
use System\Security\Hashing\DefaultHasher;

class HasherTest extends TestCase
{
    /** @test */
    public function itCanHashDefaultHasher()
    {
        $hasher = new DefaultHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /** @test */
    public function itCanHashBryptHasher()
    {
        $hasher = new BcryptHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /** @test */
    public function itCanHashArgonHasher()
    {
        $hasher = new ArgonHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /** @test */
    public function itCanHashArgon2IdHasher()
    {
        $hasher = new Argon2IdHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }
}
