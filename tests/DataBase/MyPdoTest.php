<?php

declare(strict_types=1);

namespace System\Test\Database;

final class MyPdoTest extends TestDatabase
{
    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 100,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }
}
