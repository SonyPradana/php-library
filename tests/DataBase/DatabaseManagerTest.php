<?php

declare(strict_types=1);

namespace System\Test\Database;

use System\Database\DatabaseManager;

final class DatabaseManagerTest extends TestDatabase
{
    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetDefaultConnection()
    {
        $db = new DatabaseManager([
            'testing' => $this->env,
        ]);
        $db->setDefaultConnection($this->pdo);
        $this->assertTrue($db->query('SELECT * FROM users')->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetConnection()
    {
        $db = new DatabaseManager([
            'testing' => $this->env,
        ]);
        $this->assertTrue($db->connection('testing')->query('SELECT * FROM users')->execute());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanThrowExceptionWhenConnectionNotConfigure()
    {
        $db = new DatabaseManager([
            'invalid' => null,
        ]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Database connection [invalid] not configured.');

        $this->assertTrue($db->connection('invalid')->query('SELECT * FROM users')->execute());
    }
}
