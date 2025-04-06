<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;
use System\Database\MyQuery\Join\InnerJoin;
use System\Test\Database\Asserts\UserTrait;
use System\Test\Database\TestDatabase;

final class SelectTest extends TestDatabase
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
    public function itCanSelectQuery()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryOnlyuser()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayNotHasKey('password', $users[0]);
        $this->assertArrayNotHasKey('stat', $users[0]);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithBetween()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->between('stat', 0, 100)
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithCompare()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->compare('user', '=', 'taylor')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithEqual()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithIn()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->in('user', ['taylor'])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithLike()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->like('user', 'tay%')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithWhere()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithMultyCondition()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->compare('stat', '>', 1)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithLimit()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->limit(0, 1)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithOffset()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->limitStart(0)
            ->offset(1)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectQueryWithLimitOffset()
    {
        $users = MyQuery::from('users', $this->pdo)
            ->select()
            ->limitOffset(0, 10)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * @test
     *
     * @group database
     */
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

    /**
     * @test
     *
     * @group database
     */
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
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
        $this->assertArrayHasKey('real_name', $users[0]);
    }
}
