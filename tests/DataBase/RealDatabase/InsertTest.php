<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;

final class InsertTest extends \RealDatabaseConnectionTest
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanInsertData()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user' => 'sony',
                'pwd'  => 'secret',
                'stat' => 99,
            ])
            ->execute();

        $this->assertUserExist('sony');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanInsertMultyRaw()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->rows([
                [
                    'user' => 'sony',
                    'pwd'  => 'secret',
                    'stat' => 1,
                ], [
                    'user' => 'pradana',
                    'pwd'  => 'secret',
                    'stat' => 2,
                ],
            ])
            ->execute();

        $this->assertUserExist('sony');
        $this->assertUserExist('pradana');
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
                'user' => 'sony',
                'pwd'  => 'secret',
                'stat' => 99,
            ])
            ->execute();

        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user' => 'sony',
                'pwd'  => 'secret',
                'stat' => 66,
            ])
            ->on('stat')
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
                'user' => 'sony',
                'pwd'  => 'secret',
                'stat' => 99,
            ])
            ->execute();

        MyQuery::from('users', $this->pdo)
            ->insert()
            ->rows([
                [
                    'user' => 'sony',
                    'pwd'  => 'secret',
                    'stat' => 66,
                ],
                [
                    'user' => 'sony2',
                    'pwd'  => 'secret',
                    'stat' => 66,
                ],
            ])
            ->on('user')
            ->on('stat')
            ->execute();

        $this->assertUserStat('sony', 66);
        $this->assertUserExist('sony2');
    }
}
