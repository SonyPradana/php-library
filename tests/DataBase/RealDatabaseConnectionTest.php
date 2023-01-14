<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MySchema;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertTrue;

abstract class RealDatabaseConnectionTest extends TestCase
{
    private $env;
    protected MyPDO $pdo;
    protected MySchema\MyPDO $pdo_schema;
    protected MySchema $schema;

    protected function setUp(): void
    {
        $this->env = [
            'host'           => 'localhost',
            'user'           => 'root',
            'password'       => '',
            'database_name'  => 'testing_db',
        ];

        $this->pdo_schema = new MySchema\MyPDO($this->env);
        $this->schema     = new MySchema($this->pdo_schema);

        // building the database
        $this->schema->database()->create('testing_db')->ifNotExists()->execute();

        $this->pdo        = new MyPDO($this->env);

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
        $this->schema->database()->drop('testing_db')->ifExists()->execute();
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

    protected function assertDbExists(string $database_name)
    {
        $a = $this->pdo_schema->query('SHOW DATABASES LIKE ' . $database_name)->resultset();

        assertCount(1, $a);
    }
}
