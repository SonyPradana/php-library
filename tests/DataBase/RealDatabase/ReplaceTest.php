<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;
use System\Test\Database\Asserts\UserTrait;
use System\Test\Database\TestDatabase;

final class ReplaceTest extends TestDatabase
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
    public function itCanReplaceOnNewData()
    {
        MyQuery::from('users', $this->pdo)
            ->replace()
            ->values([
                'user'      => 'sony',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        $this->assertUserExist('sony');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanReplaceOnExistData()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user'      => 'sony',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        MyQuery::from('users', $this->pdo)
            ->replace()
            ->values([
                'user'      => 'sony',
                'password'  => 'secret',
                'stat'      => 66,
            ])
            ->execute();

        $this->assertUserStat('sony', 66);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateInsertusingOneQuery()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user'      => 'sony',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        MyQuery::from('users', $this->pdo)
            ->replace()
            ->rows([
                [
                    'user'      => 'sony',
                    'password'  => 'secret',
                    'stat'      => 66,
                ],
                [
                    'user'      => 'sony2',
                    'password'  => 'secret',
                    'stat'      => 66,
                ],
            ])
            ->execute();

        $this->assertUserStat('sony', 66);
        $this->assertUserExist('sony2');
    }
}
