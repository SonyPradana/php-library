<?php

declare(strict_types=1);

use System\Database\MyQuery;

final class DeleteTest extends RealDatabaseConnectionTest
{
    /** @test */
    public function itCanDelete()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithBetween()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->between('stat', 0, 100)
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithCompare()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->compare('user', '=', 'taylor')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithEqual()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->equal('user', 'taylor')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithIn()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->in('user', ['taylor'])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithLike()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->like('user', 'tay%')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithWhere()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->where('`user` = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /** @test */
    public function itCanDeleteWithMultyCondition()
    {
        MyQuery::from('users', $this->pdo)
            ->delete()
            ->compare('stat', '>', 1)
            ->where('`user` = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }
}
