<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Test\Database\Asserts\UserTrait;
use System\Test\Database\TestDatabase;

final class LogsTest extends TestDatabase
{
    use UserTrait;

    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => 'secret',
                'stat'     => 99,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    private function profileFactory()
    {
        // factory
        $this->pdo
            ->query('CREATE TABLE profiles (
                user varchar(32) NOT NULL,
                real_name varchar(500) NOT NULL,
                PRIMARY KEY (user)
              )')
            ->execute();

        $this->pdo
            ->query('INSERT INTO profiles (
                user,
                real_name
              ) VALUES (
                :user,
                :real_name
              )')
            ->bind(':user', 'taylor')
            ->bind(':real_name', 'taylor otwell')
            ->execute();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetLogExcutionConnention()
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
        $get_logs = (fn () => $this->{'logs'})->call($this->pdo);

        // before calculate
        foreach ($get_logs as $key => $log) {
            $this->assertNull($log['time']);
        }

        // after calculate
        foreach ($this->pdo->getLogs() as $key => $log) {
            $this->assertEquals($log['query'], $logs[$key]);
            $this->assertNotNull($log['time']);
        }
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQuery()
    {
        $this->assertNotEmpty($this->pdo->getLogs());
        foreach ($this->pdo->getLogs() as $key => $log) {
            $this->assertArrayHasKey('query', $log);
            $this->assertArrayHasKey('time_start', $log);
            $this->assertArrayHasKey('time_end', $log);
            $this->assertArrayHasKey('time', $log);
        }
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFlush()
    {
        $this->assertNotEmpty($this->pdo->getLogs());
        $this->pdo->flushLogs();
        $this->assertEmpty($this->pdo->getLogs());
    }
}
