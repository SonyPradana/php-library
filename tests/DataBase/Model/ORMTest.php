<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyModel\ORM;

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
}
