<?php

declare(strict_types=1);

namespace System\Test\Database\Asserts;

use System\Database\MyQuery;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertTrue;

trait ConnectionAssertation
{
    // assert

    protected function assertUserExist(string $user)
    {
        $data  = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 1, 'expect user exist in database');
    }

    protected function assertUserNotExist(string $user)
    {
        $data  = MyQuery::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 0, 'expect user exist in database');
    }

    protected function assertUserStat($user, $expect)
    {
        $data  = MyQuery::from('users', $this->pdo)
            ->select(['stat'])
            ->equal('user', $user)
            ->all();

        $this->assertEquals($expect, (int) $data[0]['stat']);
    }

    protected function assertDbExists(string $database_name)
    {
        $a = $this->pdo_schema->query('SHOW DATABASES LIKE ' . $database_name)->resultset();

        assertCount(1, $a);
    }
}
