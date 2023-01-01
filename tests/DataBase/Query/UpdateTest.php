<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;

final class UpdateTest extends \QueryStringTest
{
    /** @test */
    public function itCanUpdateBetween()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end)',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE `test` SET `a` = 'b' WHERE (`test`.`column_1` BETWEEN 1 AND 100)",
            $update->queryBind()
        );
    }

    /** @test */
    public function itCanUpdateCompare()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE ( (test.column_1 = :column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE `test` SET `a` = 'b' WHERE ( (test.column_1 = 100) )",
            $update->queryBind()
        );
    }

    /** @test */
    public function itCanUpdateEqual()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE ( (test.column_1 = :column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE `test` SET `a` = 'b' WHERE ( (test.column_1 = 100) )",
            $update->queryBind()
        );
    }

    /** @test */
    public function itCanUpdateIn()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE (`test`.`column_1` IN (:in_0, :in_1))',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE `test` SET `a` = 'b' WHERE (`test`.`column_1` IN (1, 2))",
            $update->queryBind()
        );
    }

    /** @test */
    public function itCanUpdateLike()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE ( (test.column_1 LIKE :column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE `test` SET `a` = 'b' WHERE ( (test.column_1 LIKE 'test') )",
            $update->queryBind()
        );
    }

    /** @test */
    public function itCanUpdateWhere()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE a < :a OR b > :b',
            $update->__toString(),
            'update with where statment is like'
        );

        $this->assertEquals(
            "UPDATE `test` SET `a` = 'b' WHERE a < 1 OR b > 2",
            $update->queryBind(),
            'update with where statment is like'
        );
    }

    /** @test */
    public function itCorrectUpdateWithStrictOff(): void
    {
        $this->markTestIncomplete('support strict mode in update query');

        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'Update `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )',
            $update,
            'update statment must have using or statment'
        );

        $this->assertEquals(
            "Update `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = 123) OR (test.column_2 = 'abc') )",
            $update->queryBind(),
            'update statment must have using or statment'
        );
    }
}
