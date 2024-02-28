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
        $user = new User($this->pdo, []);
        $user->indentifer()->equal('user', 'taylor');
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
        $user = new User($this->pdo, []);

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
    public function itCanCheckColumnIsExist()
    {
        $user = $this->user();

        $this->assertTrue($user->isExist());
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

    /**
     * @test
     *
     * @group database
     */
    public function itCanConvertToArray()
    {
        $user = $this->user();

        $this->assertEquals([
            [
                'user' => 'taylor',
                'stat' => 100,
            ],
        ], $user->toArray());
        $this->assertIsIterable($user);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetFirstPrimeryKey()
    {
        $user = $this->user();

        $this->assertEquals('taylor', $user->getPrimeryKey());
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
    public function itCanCheckExist()
    {
        $user = $this->user();

        $this->assertTrue($user->has('user'));
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
        $user = $this->user();

        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals($columns[0]['stat'], $user['stat']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSetUsingArray()
    {
        $user = $this->user();

        $user['stat'] = 80;
        $columns      = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(80, $columns[0]['stat']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanCheckUsingMagicIsset()
    {
        $user = $this->user();
        $this->assertTrue(isset($user['user']));
    }

    /**
     * Unset is not perform anythink.
     *
     * @test
     *
     * @group database
     */
    public function itCanUnsetUsingArray()
    {
        $user = $this->user();

        unset($user['stat']);
        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(100, $columns[0]['stat']);
    }

    // still can get collection

    /**
     * @test
     *
     * @group database
     */
    public function itCanGetCollection()
    {
        $user = $this->user();

        $columns = (fn () => $this->{'columns'})->call($user);
        $models  = $user->get()->toArray();

        // tranform to column
        $arr = [];
        foreach ($models as $new) {
            $arr[]= (fn () => $this->{'columns'})->call($new)[0];
        }
        $this->assertEquals($columns, $arr);
    }

    // find user by some condition (static)

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindUsingId()
    {
        $user = User::find('taylor', $this->pdo);

        $this->assertTrue($user->has('user'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindUsingWhere()
    {
        $user = User::where('user = :user', [
            'user' => 'taylor',
        ], $this->pdo);

        $this->assertTrue($user->has('user'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindUsingEqual()
    {
        $user = User::equal('user', 'taylor', $this->pdo);

        $this->assertTrue($user->has('user'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindAll()
    {
        $columns = (fn () => $this->{'columns'})->call($this->user());
        $models  = User::all($this->pdo)->toArray();

        // tranform to column
        $arr = [];
        foreach ($models as $new) {
            $arr[]= (fn () => $this->{'columns'})->call($new)[0];
        }
        $this->assertEquals($columns, $arr);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindOrCreate()
    {
        $user = User::findOrCreate('taylor', [
            'user'     => 'taylor',
            'password' => 'password',
            'stat'     => 100,
        ], $this->pdo);

        $this->assertTrue($user->isExist());
        $this->assertEquals('taylor', $user->getter('user', 'nuno'));
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanFindOrCreateButNotExits()
    {
        $user = User::findOrCreate('pradana', [
            'user'     => 'pradana',
            'password' => 'password',
            'stat'     => 100,
        ], $this->pdo);

        $this->assertTrue($user->isExist());
        $this->assertEquals('pradana', $user->getter('user', 'nuno'));
    }
}

class User extends Model
{
    protected string $table_name  = 'users';
    protected string $primery_key = 'user';
    /** @var string[] Hide from shoing column */
    protected $stash = ['password'];
}
