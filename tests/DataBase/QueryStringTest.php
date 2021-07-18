<?php

use PHPUnit\Framework\TestCase;
use System\Database\MyQuery;

class QueryStringTest extends TestCase
{
  /**
   * @test
   */
  public function it_correct_select_query(): void
  {
    require_once dirname(__DIR__) . '/DataBase/Helper.php';

    $select = array();

    // tester

    $select['between'] = MyQuery::from("test")
      ->select()
      ->between("column_1", 1, 100)
      ->__toString();

    $select['compare'] = MyQuery::from("test")
      ->select()
      ->between("column_1", 1, 100)
      ->__toString();

    $select['equal'] = MyQuery::from("test")
      ->select()
      ->equal("column_1", "test")
      ->__toString();

    $select['in'] = MyQuery::from("test")
      ->select()
      ->in("column_1", [1, 2, 3, 4])
      ->__toString();

    $select['like'] = MyQuery::from("test")
      ->select()
      ->like("column_1", "test")
      ->__toString();

    $select['where'] = MyQuery::from("test")
      ->select()
      ->where("a < :a OR b > :b", ['a' => 1, 'b' => 2])
      ->__toString();

    // assertation

    $this->assertEquals(
      "SELECT * FROM `test` WHERE  (`test`.`column_1` BETWEEN :b_start AND :b_end)  ",
      $select['between'],
      "select with where statment is between"
    );

    $this->assertEquals(
      "SELECT * FROM `test` WHERE  (`test`.`column_1` BETWEEN :b_start AND :b_end)  ",
      $select['compare'],
      "select with where statment is compare"
    );

    $this->assertEquals(
      "SELECT * FROM `test` WHERE  (`test`.`column_1` IN (:in_0, :in_1, :in_2, :in_3))  ",
      $select['in'],
      "select with where statment is in"
    );

    $this->assertEquals(
      "SELECT * FROM `test` WHERE ( (test.column_1 = :column_1) )  ",
      $select['equal'],
      "select with where statment is equal"
    );

    $this->assertEquals(
      "SELECT * FROM `test` WHERE ( (test.column_1 LIKE :column_1) )  ",
      $select['like'],
      "select with where statment is like"
    );

    $this->assertEquals(
      "SELECT * FROM `test` WHERE  a < :a OR b > :b  ",
      $select['where'],
      "select with where statment is like"
    );

  }

  /**
   * @test
   */
  public function it_correct_select_query_and_limit_order(): void
  {
    $select = MyQuery::from("test")
      ->select()
      ->between("column_1", 1, 100)
      ->limit(1, 10)
      ->order("column_1", MyQuery::ORDER_ASC)
      ->__toString();

    $this->assertEquals(
      "SELECT * FROM `test` WHERE  (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC LIMIT 1, 10",
      $select,
      "select with where statment is between"
    );
  }

  /**
   * @test
   */
  public function it_correct_select_multy_column(): void
  {
    $select = MyQuery::from("test")
      ->select(['column_1', 'column_2', 'column_3'])
      ->equal("column_1", 123)
      ->equal("column_2", 'abc')
      ->equal("column_3", true)
      ->__toString();

    $this->assertEquals(
      "SELECT `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = :column_1) AND (test.column_2 = :column_2) AND (test.column_3 = :column_3) )  ",
      $select,
      "select statment must have 3 selected query"
    );
  }

  /**
   * @test
   */
  public function it_correct_select_using_or_statment(): void
  {
    $select = MyQuery::from("test")
      ->select(['column_1', 'column_2', 'column_3'])
      ->equal("column_1", 123)
      ->equal("column_2", 'abc')
      ->strictMode(false)
      ->__toString();

    $this->assertEquals(
      "SELECT `column_1`, `column_2`, `column_3` FROM `test` WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )  ",
      $select,
      "select statment must have using or statment"
    );
  }

  /**
   * @test
   */
  public function it_correct_insert_query_multy_values(): void
  {
    $insert = MyQuery::from('test')
      ->insert()
      // insert using multy value
      ->values([
        'a' => 'b',
        'c' => 'd',
        'e' => 'f',
      ])
      // insert using single value
      ->value('g', 'h')
      ->__toString();

      $this->assertEquals(
        "INSERT INTO `test` (a, c, e, g) VALUES (:val_a, :val_c, :val_e, :val_g)",
        $insert,
        "insert must equal with query 1 new row with 2 data"
      );
  }

  /**
   * @test
   */
  public function it_correct_update_query(): void
  {
    $update = array();
    $update['between'] = MyQuery::from('test')
      ->update()
      ->value('a', 'b')
      ->between('coulumn_1', 1, 100)
      ->__toString();

    $update['compare'] = MyQuery::from('test')
      ->update()
      ->value('a', 'b')
      ->compare("ten", ">", 9)
      ->__toString();

    $update['equal'] = MyQuery::from('test')
      ->update()
      ->value('a', 'b')
      ->equal('ten', 10)
      ->__toString();

    $update['in'] = MyQuery::from('test')
      ->update()
      ->value('a', 'b')
      ->in('column_1', [1, 2, 3, 4, 5])
      ->__toString();

    $update['like'] = MyQuery::from('test')
      ->update()
      ->value('a', 'b')
      ->like('i', 'you')
      ->__toString();

    $update['where'] = MyQuery::from('test')
      ->update()
      ->value('a', 'b')
      ->where('col1 = :col1 OR col2 = :col2', ['col1' => 1, 'col2' => 2])
      ->__toString();

      // assertation

      $this->assertEquals(
        "UPDATE `test` SET `a` = :val_a WHERE  (`test`.`coulumn_1` BETWEEN :b_start AND :b_end)",
        $update['between'],
        "update query must same with between operator"
      );

      $this->assertEquals(
        "UPDATE `test` SET `a` = :val_a WHERE ( (test.ten > :ten) )",
        $update['compare'],
        "update query must same with compire operator"
      );

      $this->assertEquals(
        "UPDATE `test` SET `a` = :val_a WHERE ( (test.ten = :ten) )",
        $update['equal'],
        "update query must same with equal operator"
      );

      $this->assertEquals(
        "UPDATE `test` SET `a` = :val_a WHERE  (`test`.`column_1` IN (:in_0, :in_1, :in_2, :in_3, :in_4))",
        $update['in'],
        "update query must same with in operator"
      );

      $this->assertEquals(
        "UPDATE `test` SET `a` = :val_a WHERE  col1 = :col1 OR col2 = :col2",
        $update['where'],
        "update query must same with where operator"
      );
  }

  /**
   * @test
   */
  public function it_correct_delete_query(): void
  {
    $delete = array();
    $delete['between'] = MyQuery::from('test')
      ->delete()
      ->between('coulumn_1', 1, 100)
      ->__toString();

    $delete['compare'] = MyQuery::from('test')
      ->delete()
      ->compare("ten", ">", 9)
      ->__toString();

    $delete['equal'] = MyQuery::from('test')
      ->delete()
      ->equal('ten', 10)
      ->__toString();

    $delete['in'] = MyQuery::from('test')
      ->delete()
      ->in('column_1', [1, 2, 3, 4, 5])
      ->__toString();

    $delete['like'] = MyQuery::from('test')
      ->delete()
      ->like('i', 'you')
      ->__toString();

    $delete['where'] = MyQuery::from('test')
      ->delete()
      ->where('col1 = :col1 OR col2 = :col2', ['col1' => 1, 'col2' => 2])
      ->__toString();

      // assertation

      $this->assertEquals(
        "DELETE FROM `test` WHERE  (`test`.`coulumn_1` BETWEEN :b_start AND :b_end)",
        $delete['between'],
        "delete query must same with between operator"
      );

      $this->assertEquals(
        "DELETE FROM `test` WHERE ( (test.ten > :ten) )",
        $delete['compare'],
        "delete query must same with compire operator"
      );

      $this->assertEquals(
        "DELETE FROM `test` WHERE ( (test.ten = :ten) )",
        $delete['equal'],
        "delete query must same with equal operator"
      );

      $this->assertEquals(
        "DELETE FROM `test` WHERE  (`test`.`column_1` IN (:in_0, :in_1, :in_2, :in_3, :in_4))",
        $delete['in'],
        "delete query must same with in operator"
      );

      $this->assertEquals(
        "DELETE FROM `test` WHERE  col1 = :col1 OR col2 = :col2",
        $delete['where'],
        "delete query must same with where operator"
      );
  }
}
