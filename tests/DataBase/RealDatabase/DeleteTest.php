<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;
use System\Test\Database\Asserts\UserAssertation;
use System\Test\Database\TestDatabase;

final class DeleteTest extends TestDatabase
{
    use UserAssertation;

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
    public function itCanDelete()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithBetween()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->between('stat', 0, 100)
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithCompare()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->compare('user', '=', 'taylor')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithEqual()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->equal('user', 'taylor')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithIn()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->in('user', ['taylor'])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithLike()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->like('user', 'tay%')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithWhere()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteWithMultyCondition()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->compare('stat', '>', 1)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }
}
