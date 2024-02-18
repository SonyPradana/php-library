<?php

declare(strict_types=1);

namespace System\Test\Database\Model;

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

    /**
     * @test
     *
     * @group database
     */
    public function itCanCreateData()
    {
        $this->markTestSkipped('tdd');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanReadData()
    {
        $this->markTestSkipped('tdd');
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
        $this->markTestSkipped('tdd');
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
