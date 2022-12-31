<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;

final class SelectTest extends \QueryStringTest
{
    /** @test */
    public function itCanSelectBetween()
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end)',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN 1 AND 100)',
            $select->queryBind()
        );
    }

    /** @test */
    public function itCanSelectCompare()
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 = :column_1) )',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 = 100) )',
            $select->queryBind()
        );
    }

    /** @test */
    public function itCanSelectEqual()
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 = :column_1) )',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 = 100) )',
            $select->queryBind()
        );
    }

    /** @test */
    public function itCanSelectIn()
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` IN (:in_0, :in_1))',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` IN (1, 2))',
            $select->queryBind()
        );
    }

    /** @test */
    public function itCanSelectLike()
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 LIKE :column_1) )',
            $select->__toString()
        );

        $this->assertEquals(
            "SELECT * FROM `test` WHERE ( (test.column_1 LIKE 'test') )",
            $select->queryBind()
        );
    }

    // generate test for 'where' operator
    /** @test */
    public function itCanSelectWhere()
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'SELECT * FROM `test` WHERE a < :a OR b > :b',
            $select->__toString(),
            'select with where statment is like'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE a < 1 OR b > 2',
            $select->queryBind(),
            'select with where statment is like'
        );
    }

      /** @test */
      public function itCorrectSelectMultyColumn(): void
      {
          $select = MyQuery::from('test', $this->PDO)
              ->select(['column_1', 'column_2', 'column_3'])
              ->equal('column_1', 123)
              ->equal('column_2', 'abc')
              ->equal('column_3', true);

          $this->assertEquals(
              'SELECT `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = :column_1) AND (test.column_2 = :column_2) AND (test.column_3 = :column_3) )',
              $select->__toString(),
              'select statment must have 3 selected query'
          );

          //   WARNING: bolean return as boolean not 0 or 1
          //   $this->assertEquals(
        //       "SELECT `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = 123) AND (test.column_2 = 'abc') AND (test.column_3 = 1) )",
        //       $select->queryBind(),
        //       'select statment must have 3 selected query'
          //   );
      }

    /** @test */
    public function itCorrectSelectWithStrictOff(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select(['column_1', 'column_2', 'column_3'])
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'SELECT `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )',
            $select,
            'select statment must have using or statment'
        );

        $this->assertEquals(
            "SELECT `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = 123) OR (test.column_2 = 'abc') )",
            $select->queryBind(),
            'select statment must have using or statment'
        );
    }
}
