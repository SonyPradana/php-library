<?php

declare(strict_types=1);

namespace System\Test\Database\Model;

use System\Database\MyModel\Model;
use System\Database\MyQuery\Insert;
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
        $user = new User($this->pdo, [[]], ['user' => ['taylor']]);
        if ($read) {
            $user->read();
        }

        return $user;
    }

    private function createProfileSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE `profiles` (
                `user`      varchar(32)  NOT NULL,
                `name`      varchar(100) NOT NULL,
                `gender`    varchar(10) NOT NULL,
                PRIMARY KEY (`user`)
            )')
           ->execute();
    }

    private function createProfiles($profiles): bool
    {
        return (new Insert('profiles', $this->pdo))
            ->rows($profiles)
            ->execute();
    }

    private function createOrderSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE `orders` (
                `id`   varchar(3)  NOT NULL,
                `user` varchar(32)  NOT NULL,
                `name` varchar(100) NOT NULL,
                `type` varchar(30) NOT NULL,
                PRIMARY KEY (`id`)
            )')
           ->execute();
    }

    private function createOrders($orders): bool
    {
        return (new Insert('orders', $this->pdo))
            ->rows($orders)
            ->execute();
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateData()
    {
        $user = new User($this->pdo, [
            [
                'user'     => 'nuno',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 50,
            ],
        ], [[]]);

        $this->assertTrue($user->insert());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanReadData()
    {
        $user = new User($this->pdo, [[]], ['user' => ['taylor']]);

        $this->assertTrue($user->read());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateData()
    {
        $user = $this->user();

        $user->setter('stat', 75);

        $this->assertTrue($user->update());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanDeleteData()
    {
        $user = $this->user();
        $this->assertTrue($user->delete());
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
        // profile
        $profile = [
            'user'   => 'taylor',
            'name'   => 'taylor otwell',
            'gender' => 'male',
        ];
        $this->createProfileSchema();
        $this->createProfiles([$profile]);

        $user   = $this->user();
        $result = $user->hasOne('profiles', 'user');
        $this->assertEquals($profile, $result->toArray());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetHasMany()
    {
        // order
        $order = [
            [
                'id'     => '1',
                'user'   => 'taylor',
                'name'   => 'order 1',
                'type'   => 'gadget',
            ], [
                'id'     => '3',
                'user'   => 'taylor',
                'name'   => 'order 2',
                'type'   => 'gadget',
            ],
        ];
        $this->createOrderSchema();
        $this->createOrders($order);

        $user   = $this->user();
        $result = $user->hasMany('orders', 'user');
        $this->assertEquals($order, $result->toArray());
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckisClean()
    {
        $user = $this->user();
        $this->assertTrue($user->isClean(), 'Check all column');
        $this->assertTrue($user->isClean('stat'), 'Check spesifik column');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckisDirty()
    {
        $user = $this->user();
        $user->setter('stat', 75);
        $this->assertTrue($user->isDirty(), 'Check all column');
        $this->assertTrue($user->isDirty('stat'), 'Check spesifik column');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetChangeColumn()
    {
        $user = $this->user();
        $this->assertEquals([], $user->changes(), 'original fresh data');
        // modify
        $user->setter('stat', 75);
        $this->assertEquals([
            'stat' => 75,
        ], $user->changes(), 'change first column');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanHiddeColumn()
    {
        $user = $this->user();

        $this->assertArrayNotHasKey('password', $user->first(), 'password must hidden by stash');
    }

    // getter setter - should return firts query

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetUsingGetterInColumn()
    {
        $user = $this->user();

        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals($columns[0]['stat'], $user->getter('stat', 0));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetUsingSetterterInColumn()
    {
        $user = $this->user();

        $user->setter('stat', 80);
        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(80, $columns[0]['stat']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetUsingMagicGetterInColumn()
    {
        $user = $this->user();

        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals($columns[0]['stat'], $user->stat);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetUsingMagicSetterterInColumn()
    {
        $user = $this->user();

        $user->stat = 80;
        $columns    = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(80, $columns[0]['stat']);
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
