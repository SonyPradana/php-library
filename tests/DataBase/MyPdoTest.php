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

    /**
     * @test
     *
     * @group database
     */
    public function itCanLogExcutionConnention()
    {
        $this->pdo->flushLogs();
        $this->pdo->query('select * from users where user = :user')->bind('user', 'taylor')->resultset();
        $this->pdo->query('select * from users where user = :user')->bind('user', 'taylor')->single();
        $this->pdo->query('delete from users where user = :user')->bind('user', 'taylor')->execute();

        $logs = [
            'select * from users where user = :user',
            'select * from users where user = :user',
            'delete from users where user = :user',
        ];

        foreach ($this->pdo->getLogs() as $key => $log) {
            $this->assertEquals($log['query'], $logs[$key]);
        }
    }
}
