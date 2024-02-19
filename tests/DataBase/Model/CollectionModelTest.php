<?php

declare(strict_types=1);

namespace System\Test\Database\Model;

use System\Test\Database\BaseConnection;

final class CollectionModelTest extends BaseConnection
{
    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 100,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    public function user(): User
    {
        $user = new User('user', [[]], $this->pdo, ['user' => ['taylor']]);
        $user->read();

        return $user;
    }

    // item collection test

    /**
     * @test
     *
     * @group database
     */
    public function shouldReturnModelEveryItems()
    {
        $users = $this->user();

        foreach ($users->get() as $user) {
            $this->assertTrue($user instanceof User);
        }
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetAllIds()
    {
        $this->markTestSkipped('TDD');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckIsClean()
    {
        $users = $this->user();

        $this->assertTrue($users->get()->isclean());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckIsDirty()
    {
        $this->markTestSkipped('TDD');
        $users = $this->user();

        $this->assertTrue($users->get()->isDirty());
    }

    // crud eager load

    /**
     * @test
     *
     * @group database
     */
    public function itCanReadData()
    {
        $users = $this->user();

        foreach ($users->get() as $user) {
            $this->assertTrue($user->read());
        }
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateData()
    {
        $this->markTestSkipped('TDD');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteData()
    {
        $users = $this->user();

        foreach ($users->get() as $user) {
            $this->assertTrue($user->delete());
        }
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetHasOne()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetHasMany()
    {
        $this->markTestSkipped('tdd');
    }

    // crud upstream

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateAllWithSingleQuery()
    {
        $this->markTestSkipped('TDD');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteAllWithSingleQuery()
    {
        $this->markTestSkipped('TDD');
    }
}
