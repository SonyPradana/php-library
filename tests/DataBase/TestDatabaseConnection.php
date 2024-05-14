<?php

declare(strict_types=1);

use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MySchema;
use System\Test\Database\BaseConnection;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertTrue;

abstract class TestDatabaseConnection extends BaseConnection
{
    protected function setUp(): void
    {
        $this->createConnection();
        $this
           ->getPdo()
           ->query('CREATE TABLE `users` (
                `user`      varchar(32)  NOT NULL,
                `pwd`       varchar(500) NOT NULL,
                `stat`      int(2)       NOT NULL,
                PRIMARY KEY (`user`)
            )')
           ->execute();

        $this->createUser([
            [
                'user' => 'taylor',
                'pwd'  => 'secret',
                'stat' => 99,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    protected function getPdo(): MyPDO
    {
        return $this->pdo;
    }

    protected function getPdoSchema(): MySchema\MyPDO
    {
        return $this->pdo_schema;
    }

    protected function assertUserExist(string $user)
    {
        $data  = MyQuery::from('users', $this->getPdo())
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 1, 'expect user exist in database');
    }

    protected function assertUserNotExist(string $user)
    {
        $data  = MyQuery::from('users', $this->getPdo())
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 0, 'expect user exist in database');
    }

    protected function assertUserStat($user, $expect)
    {
        $data  = MyQuery::from('users', $this->getPdo())
            ->select(['stat'])
            ->equal('user', $user)
            ->all();

        $this->assertEquals($expect, (int) $data[0]['stat']);
    }

    protected function assertDbExists(string $database_name)
    {
        $a = $this->getPdoSchema()->query('SHOW DATABASES LIKE ' . $database_name)->resultset();

        assertCount(1, $a);
    }
}
