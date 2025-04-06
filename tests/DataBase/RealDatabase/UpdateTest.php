<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;
use System\Test\Database\Asserts\UserTrait;
use System\Test\Database\TestDatabase;

final class UpdateTest extends TestDatabase
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

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdate()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithBetween()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->between('stat', 0, 100)
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithCompare()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->compare('user', '=', 'taylor')
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithEqual()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->equal('user', 'taylor')
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithIn()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->in('user', ['taylor'])
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithLike()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->like('user', 'tay%')
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithWhere()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateWithMultyCondition()
    {
        MyQuery::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->compare('stat', '>', 1)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }
}
