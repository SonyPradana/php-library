<?php

declare(strict_types=1);

use System\Database\MyQuery;
use System\Database\MyQuery\Join\InnerJoin;

final class SelectTest extends RealDatabaseConnectionTest
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

    /** @test */
    public function itCanSelectQuery()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('pwd', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /** @test */
    public function itCanSelectQueryOnlyuser()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayNotHasKey('pwd', $users[0]);
        $this->assertArrayNotHasKey('stat', $users[0]);
    }

    /** @test */
    public function itCanSelectQueryWithBetween()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->between('stat', 0, 100)
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithCompare()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->compare('user', '=', 'taylor')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithEqual()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithIn()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->in('user', ['taylor'])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithLike()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->like('user', 'tay%')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithWhere()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->where('`user` = :user', [
                [':user', 'taylor'],
            ])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithMultyCondition()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->compare('stat', '>', 1)
            ->where('`user` = :user', [
                [':user', 'taylor'],
            ])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectQueryWithLimit()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->limit(0, 1)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('pwd', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /** @test */
    public function itCanSelectQueryWithStritMode()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->equal('stat', 99)
            ->strictMode(false)
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /** @test */
    public function itCanSelectJoin()
    {
        $this->profileFactory();

        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->join(InnerJoin::ref('profiles', 'user '))
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('pwd', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
        $this->assertArrayHasKey('real_name', $users[0]);
    }
}