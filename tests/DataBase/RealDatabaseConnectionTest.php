<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;
use System\Database\MyQuery;

use function PHPUnit\Framework\assertTrue;

abstract class RealDatabaseConnectionTest extends TestCase
{
    private $env;
    protected MyPDO $pdo;

    protected function setUp(): void
    {
        $this->env = [
            'host'           => 'localhost',
            'user'           => 'root',
            'password'       => '',
            'database_name'  => 'testing_db',
        ];

        $this->schema()->query('DROP DATABASE IF EXISTS testing_db;')->execute();
        $this->schema()->query('CREATE DATABASE IF NOT EXISTS testing_db;')->execute();

        $this->pdo = new MyPDO($this->env);

        // factory
        $this->pdo
            ->query('CREATE TABLE `users` (
                `user` varchar(32) NOT NULL,
                `pwd` varchar(500) NOT NULL,
                `stat` int(2) NOT NULL,
                PRIMARY KEY (`user`)
              )')
            ->execute();

        $this->pdo
            ->query('INSERT INTO `users` (
                `user`,
                `pwd`,
                `stat`
              ) VALUES (
                :user,
                :pwd,
                :stat
              )')
            ->bind(':user', 'taylor')
            ->bind(':pwd', 'secret')
            ->bind(':stat', 99)
            ->execute();
    }

    protected function tearDown(): void
    {
        $this->schema()->query('DROP DATABASE IF EXISTS testing_db;')->execute();
    }

    private function schema()
    {
        $host = $this->env['host'];
        $user = $this->env['user'];
        $pass = $this->env['password'];

        return new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    }

    // assert

    protected function assertUserExist(string $user)
    {
        $data  = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 1, 'expect user exist in database');
    }

    protected function assertUserNotExist(string $user)
    {
        $data  = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 0, 'expect user exist in database');
    }

    protected function assertUserStat($user, $expect)
    {
        $data  = MyQuery::from('users', $this->pdo)
            ->select(['stat'])
            ->equal('user', $user)
            ->all();

        $this->assertEquals($expect, (int) $data[0]['stat']);
    }
}
