<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyModel\ORM;
use System\Database\MyQuery;

final class ORMTest extends \RealDatabaseConnectionTest
{
    private function profileFactory()
    {
        // factory
        $this->pdo
            ->query('CREATE TABLE `profiles` (
                `user` varchar(32) NOT NULL,
                `real_name` varchar(500) NOT NULL,
                PRIMARY KEY (`user`)
              )')
            ->execute();

        $this->pdo
            ->query('INSERT INTO `profiles` (
                `user`,
                `real_name`
              ) VALUES (
                :user,
                :real_name
              )')
            ->bind(':user', 'taylor')
            ->bind(':real_name', 'taylor otwell')
            ->execute();
    }

    private function commentsFactory()
    {
        // factory
        $this->pdo
            ->query('CREATE TABLE `comments` (
                `id` int NOT NULL AUTO_INCREMENT,
                `user` varchar(32) NOT NULL,
                `comment` varchar(100),
                PRIMARY KEY (`id`)
              )')
            ->execute();

        $this->pdo
            ->query('INSERT INTO `comments` (
                `id`,
                `user`,
                `comment`
              ) VALUES (
                :id,
                :user,
                :comment
              )')
            ->bind(':id', 1)
            ->bind(':user', 'taylor')
            ->bind(':comment', 'test123')
            ->execute()
        ;
    }

    /**
     * @test
     *
     * @group database
     */
    public function itOrmCanReadData()
    {
        $orm = new ORM('users', [], $this->pdo, ['user' => 'taylor'], 'user');

        $this->assertTrue($orm->read());
        $this->assertEquals('secret', $orm->pwd);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itOrmCanUpdateData()
    {
        $orm = new ORM('users', [], $this->pdo, ['user' => 'taylor'], 'user');
        $orm->read();
        $this->assertEquals(99, $orm->stat);

        $orm->stat = 50;
        $this->assertTrue($orm->update());
        $this->assertEquals(50, $orm->stat);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itOrmCanDeleteData()
    {
        $orm = new ORM('users', [], $this->pdo, ['user' => 'taylor'], 'user');
        $orm->read();

        $this->assertTrue($orm->delete());

        $user = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', 'taylor')
            ->get()
        ;
        $this->assertEquals(0, $user->count());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itOrmCanGetHasOne()
    {
        $this->profileFactory();
        $orm = new ORM('users', [], $this->pdo, ['user' => 'taylor'], 'user');
        $orm->read();

        $this->assertEquals(
            [
                'user'      => 'taylor',
                'real_name' => 'taylor otwell',
                'pwd'       => 'secret',
                'stat'      => 99,
            ],
            $orm->hasOne('profiles', 'user')->toArray()
        );
    }

    /**
     * @test
     *
     * @group database
     */
    public function itOrmCanGetHasMany()
    {
        $this->profileFactory();
        $this->commentsFactory();
        $orm = new ORM('users', [], $this->pdo, ['user' => 'taylor'], 'user');
        $orm->read();
        $this->assertEquals(
            [
                'user'      => 'taylor',
                'pwd'       => 'secret',
                'stat'      => 99,
                'comments'  => [
                    [
                        'id'      => 1,
                        'user'    => 'taylor',
                        'comment' => 'test123',
                    ],
                ],
            ],
            $orm->hasMany('comments', 'user')->toArray()
        );
    }
}
