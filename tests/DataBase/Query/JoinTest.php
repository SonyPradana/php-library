<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;
use System\Database\MyQuery\InnerQuery;
use System\Database\MyQuery\Join\CrossJoin;
use System\Database\MyQuery\Join\FullJoin;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Join\LeftJoin;
use System\Database\MyQuery\Join\RightJoin;
use System\Database\MyQuery\Select;

final class JoinTest extends \QueryStringTest
{
    /** @test */
    public function itCanGenerateInnerJoin()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateLeftJoin()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(LeftJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` LEFT JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` LEFT JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateRightJoin()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(RightJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` RIGHT JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` RIGHT JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateFullJoin()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(FullJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` FULL OUTER JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` FULL OUTER JOIN `join_table` ON `base_table`.`base_id` = `join_table`.`join_id`',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateCrossJoin()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(CrossJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` CROSS JOIN `join_table`',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` CROSS JOIN `join_table`',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanJoinMultyple()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(InnerJoin::ref('join_table_1', 'base_id', 'join_id'))
            ->join(InnerJoin::ref('join_table_2', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN `join_table_1` ON `base_table`.`base_id` = `join_table_1`.`join_id` INNER JOIN `join_table_2` ON `base_table`.`base_id` = `join_table_2`.`join_id`',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN `join_table_1` ON `base_table`.`base_id` = `join_table_1`.`join_id` INNER JOIN `join_table_2` ON `base_table`.`base_id` = `join_table_2`.`join_id`',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanJoinWithCondition()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->equal('a', 1)
            ->join(InnerJoin::ref('join_table_1', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN `join_table_1` ON `base_table`.`base_id` = `join_table_1`.`join_id` WHERE ( (`base_table`.`a` = :a) )',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN `join_table_1` ON `base_table`.`base_id` = `join_table_1`.`join_id` WHERE ( (`base_table`.`a` = 1) )',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateInnerJoinWithSubQuery()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(InnerJoin::ref(
                new InnerQuery(
                    (new Select('join_table', ['join_id'], $this->PDO))->in('join_id', [1, 2]),
                    'join_table'
                ),
                'base_id',
                'join_id'
            ))
            ->order('base_id')
        ;

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN (SELECT `join_id` FROM `join_table` WHERE (`join_table`.`join_id` IN (:in_0, :in_1))) AS `join_table` ON `base_table`.`base_id` = `join_table`.`join_id` ORDER BY `base_table`.`base_id` ASC',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN (SELECT `join_id` FROM `join_table` WHERE (`join_table`.`join_id` IN (1, 2))) AS `join_table` ON `base_table`.`base_id` = `join_table`.`join_id` ORDER BY `base_table`.`base_id` ASC',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateInnerJoinInDeleteClausa()
    {
        $join = MyQuery::from('base_table', $this->PDO)
            ->delete()
            ->alias('bt')
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'))
            ->equal('join_table.a', 1)
        ;

        $this->assertEquals(
            'DELETE `bt` FROM `base_table` AS `bt` INNER JOIN `join_table` ON `bt`.`base_id` = `join_table`.`join_id` WHERE ( (`join_table`.`a` = :join_table__a) )',
            $join->__toString()
        );

        $this->assertEquals(
            'DELETE `bt` FROM `base_table` AS `bt` INNER JOIN `join_table` ON `bt`.`base_id` = `join_table`.`join_id` WHERE ( (`join_table`.`a` = 1) )',
            $join->queryBind()
        );
    }

    /** @test */
    public function itCanGenerateInnerJoinInUpdateClausa()
    {
        $update = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'))
            ->equal('test.column_1', 100)
        ;

        $this->assertEquals(
            'UPDATE `test` INNER JOIN `join_table` ON `test`.`base_id` = `join_table`.`join_id` SET `a` = :bind_a WHERE ( (`test`.`column_1` = :test__column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            'UPDATE `test` INNER JOIN `join_table` ON `test`.`base_id` = `join_table`.`join_id` SET `a` = \'b\' WHERE ( (`test`.`column_1` = 100) )',
            $update->queryBind()
        );
    }
}
