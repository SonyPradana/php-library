<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;

final class LimitTest extends \TestQueryString
{
    /** @test */
    public function itCorrectSelectQueryWithLimitOrder(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(1, 10)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC LIMIT 1, 10',
            $select->__toString(),
            'select with where statment is between'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN 1 AND 100) ORDER BY `test`.`column_1` ASC LIMIT 1, 10',
            $select->queryBind(),
            'select with where statment is between'
        );
    }

    /** @test */
    public function itCorrectSelectQueryWithLimitEndOrderWIthLimitEndLessThatZero(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(2, -1)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC LIMIT 2, 0',
            $select->__toString(),
            'select with where statment is between'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN 1 AND 100) ORDER BY `test`.`column_1` ASC LIMIT 2, 0',
            $select->queryBind(),
            'select with where statment is between'
        );
    }

    /** @test */
    public function itCorrectSelectQueryWithLimitStartLessThatZero(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(-1, 2)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC LIMIT 2',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN 1 AND 100) ORDER BY `test`.`column_1` ASC LIMIT 2',
            $select->queryBind()
        );
    }

    /** @test */
    public function itCorrectSelectQueryWithLimitAndOffet(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
            ->limitStart(1)
            ->offset(10)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC LIMIT 1 OFFSET 10',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN 1 AND 100) ORDER BY `test`.`column_1` ASC LIMIT 1 OFFSET 10',
            $select->queryBind()
        );
    }

    /** @test */
    public function itCorrectSelectQueryWithLimitStartAndLimitEndtLessThatZero(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(-1, -1)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC',
            $select->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC',
            $select->__toString()
        );
    }
}
