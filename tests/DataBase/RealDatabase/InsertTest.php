<?php

declare(strict_types=1);

use System\Database\MyQuery;

final class InsertTest extends RealDatabaseConnectionTest
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
}
