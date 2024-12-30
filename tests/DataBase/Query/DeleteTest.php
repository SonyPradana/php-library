<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;

final class DeleteTest extends \QueryStringTest
{
    /** @test */
    public function itCanDeleteBetween()
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'DELETE FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end)',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE (`test`.`column_1` BETWEEN 1 AND 100)',
            $delete->queryBind()
        );
    }

    /** @test */
    public function itCanDeleteCompare()
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (`test`.column_1 = :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (`test`.column_1 = 100) )',
            $delete->queryBind()
        );
    }

    /** @test */
    public function itCanDeleteEqual()
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (`test`.column_1 = :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (`test`.column_1 = 100) )',
            $delete->queryBind()
        );
    }

    /** @test */
    public function itCanDeleteIn()
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'DELETE FROM `test` WHERE (`test`.`column_1` IN (:in_0, :in_1))',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE (`test`.`column_1` IN (1, 2))',
            $delete->queryBind()
        );
    }

    /** @test */
    public function itCanDeleteLike()
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (`test`.column_1 LIKE :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            "DELETE FROM `test` WHERE ( (`test`.column_1 LIKE 'test') )",
            $delete->queryBind()
        );
    }

    /** @test */
    public function itCanDeleteWhere()
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'DELETE FROM `test` WHERE a < :a OR b > :b',
            $delete->__toString(),
            'update with where statment is like'
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE a < 1 OR b > 2',
            $delete->queryBind(),
            'update with where statment is like'
        );
    }

    /** @test */
    public function itCorrectDeleteWithStrictOff(): void
    {
        $delete = MyQuery::from('test', $this->PDO)
            ->delete()
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (`test`.column_1 = :column_1) OR (`test`.column_2 = :column_2) )',
            $delete->__toString(),
            'update statment must have using or statment'
        );

        $this->assertEquals(
            "DELETE FROM `test` WHERE ( (`test`.column_1 = 123) OR (`test`.column_2 = 'abc') )",
            $delete->queryBind(),
            'update statment must have using or statment'
        );
    }
}
