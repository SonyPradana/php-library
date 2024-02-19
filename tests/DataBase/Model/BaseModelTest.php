<?php

declare(strict_types=1);

namespace System\Test\Database\Model;

use System\Database\MyModel\Model;
use System\Test\Database\BaseConnection;

final class BaseModelTest extends BaseConnection
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

    public function user(bool $read = true): User
    {
        $user = new User('user', [[]], $this->pdo, ['user' => ['taylor']]);
        if ($read) {
            $user->read();
        }

        return $user;
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateData()
    {
        $user = new User('users', [
            [
                'user'     => 'nuno',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 50,
            ],
        ], $this->pdo);

        $this->assertTrue($user->insert());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanReadData()
    {
        $user = new User('user', [[]], $this->pdo, ['user' => ['taylor']]);

        $this->assertTrue($user->read());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateData()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteData()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetFirst()
    {
        $users = $this->user();

        $this->assertEquals([
            'user' => 'taylor',
            'stat' => 100,
        ], $users->first());
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

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckisClean()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckisDirty()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetChangeColumn()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetHiddeColumn()
    {
        $this->markTestSkipped('tdd');
    }

    // getter setter - should return firts query

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetUsingGetterInColumn()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetUsingSetterterInColumn()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetUsingMagicGetterInColumn()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetUsingMagicSetterterInColumn()
    {
        $this->markTestSkipped('tdd');
    }

    // array access

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetUsingArray()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetUsingArray()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckUsingArray()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUnsetUsingArray()
    {
        $this->markTestSkipped('tdd');
    }

    // still can get collection

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetCollection()
    {
        $this->markTestSkipped('tdd');
    }

    // find user by some condition (static)

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindUsingId()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindUingWhere()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindOrCreate()
    {
        $this->markTestSkipped('tdd');
    }
}

class User extends Model
{
    protected string $table_name  = 'users';
    protected string $primery_key = 'user';
    /** @var string[] Hide from shoing column */
    protected $stash = ['password'];
}
