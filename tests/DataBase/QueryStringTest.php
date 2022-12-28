<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\CrossJoin;
use System\Database\MyQuery\Join\FullJoin;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Join\LeftJoin;
use System\Database\MyQuery\Join\RightJoin;
use System\Database\MyQuery\Select;

final class QueryStringTest extends TestCase
{
    private $PDO;

    protected function setUp(): void
    {
        $this->PDO = Mockery::mock(MyPDO::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function itCorrectSelectQuery(): void
    {
        $select = [];

        // tester

        $select['between'] = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100);

        $select['compare'] = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100);

        $select['equal'] = MyQuery::from('test', $this->PDO)
            ->select()
            ->equal('column_1', 'test');

        $select['in'] = MyQuery::from('test', $this->PDO)
            ->select()
            ->in('column_1', [1, 2, 3, 4]);

        $select['like'] = MyQuery::from('test', $this->PDO, $this->PDO)
            ->select()
            ->like('column_1', 'test');

        $select['where'] = MyQuery::from('test', $this->PDO, $this->PDO)
            ->select()
            ->where('a < :a OR b > :b', [['a', 1], ['b', 2]]);

        // assertation

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end)',
            $select['between'],
            'select with where statment is between'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end)',
            $select['compare'],
            'select with where statment is compare'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` IN (:in_0, :in_1, :in_2, :in_3))',
            $select['in'],
            'select with where statment is in'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 = :column_1) )',
            $select['equal'],
            'select with where statment is equal'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.column_1 LIKE :column_1) )',
            $select['like'],
            'select with where statment is like'
        );

        $this->assertEquals(
            'SELECT * FROM `test` WHERE a < :a OR b > :b',
            $select['where'],
            'select with where statment is like'
        );
    }

    /** @test */
    public function itCorrectSelectQueryAndLimitOrder(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(1, 10)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE (`test`.`column_1` BETWEEN :b_start AND :b_end) ORDER BY `test`.`column_1` ASC LIMIT 1, 10',
            $select,
            'select with where statment is between'
        );
    }

    /** @test */
    public function itCorrectSelectQueryAndLimitOrderWIthLimitEndLessThatZero(): void
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
    }

    /** @test */
    public function itCorrectSelectQueryAndLimitOrderWIthLimitStartLessThatZero(): void
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
    }

    /** @test */
    public function itCorrectSelectQueryAndLimitOrderWIthLimitStartLessAndLimitEndtLessThatZero(): void
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
            $select,
            'select statment must have 3 selected query'
        );
    }

    /** @test */
    public function itCorrectSelectUsingOrStatment(): void
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
    }

    /** @test */
    public function itCorrectSelectQueryBinding(): void
    {
        $select = MyQuery::from('test', $this->PDO)
            ->select()
            ->equal('user', 'test')
            ->between('column_1', 1, 100)
            ->limit(1, 10)
            ->order('column_1', MyQuery::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM `test` WHERE ( (test.user = test) ) AND (`test`.`column_1` BETWEEN 1 AND 100) ORDER BY `test`.`column_1` ASC LIMIT 1, 10',
            $select->queryBind(),
            'select with where statment is between'
        );
    }

    /** @test */
    public function itCorrectInsertQueryMultyValues(): void
    {
        $insert = MyQuery::from('test', $this->PDO)
            ->insert()
            // insert using multy value
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
            // insert using single value
            ->value('g', 'h');

        $this->assertEquals(
            'INSERT INTO `test` (a, c, e, g) VALUES (:bind_a, :bind_c, :bind_e, :bind_g)',
            $insert->__toString(),
            'insert must equal with query 1 new row with 2 data'
        );
    }

    /** @test */
    public function itCorrectInsertQueryMultyValuesQueryBinding(): void
    {
        $insert = MyQuery::from('test', $this->PDO)
            ->insert()
            // insert using multy value
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
            // insert using single value
            ->value('g', 'h');

        $this->assertEquals(
            'INSERT INTO `test` (a, c, e, g) VALUES (b, d, f, h)',
            $insert->queryBind(),
            'insert must equal with query 1 new row with 2 data'
        );
    }

    /** @test */
    public function itCorrectInsertQueryMultyRaws(): void
    {
        $insert = MyQuery::from('test', $this->PDO)
            ->insert()
            // insert using multy value
            ->raws([
                [
                    'a' => 'b',
                    'c' => 'd',
                    'e' => 'f',
                ], [
                    'a' => 'b',
                    'c' => 'd',
                    'e' => 'f',
                ],
            ]);

        $this->assertEquals(
            'INSERT INTO `test` (a, c, e) VALUES (:bind_0_a, :bind_0_c, :bind_0_e), (:bind_1_a, :bind_1_c, :bind_1_e)',
            $insert->__toString(),
            'insert must equal with query 1 new row with 2 data'
        );
    }

    /** @test */
    public function itCorrectUpdateQuery(): void
    {
        $update            = [];
        $update['between'] = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->between('coulumn_1', 1, 100);

        $update['compare'] = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->compare('ten', '>', 9);

        $update['equal'] = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->equal('ten', 10);

        $update['in'] = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->in('column_1', [1, 2, 3, 4, 5]);

        $update['like'] = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->like('i', 'you');

        $update['where'] = MyQuery::from('test', $this->PDO)
            ->update()
            ->value('a', 'b')
            ->where('col1 = :col1 OR col2 = :col2', [['col1', 1], ['col2', 2]]);

        // assertation

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE (`test`.`coulumn_1` BETWEEN :b_start AND :b_end)',
            $update['between']->__toString(),
            'update query must same with between operator'
        );

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE ( (test.ten > :ten) )',
            $update['compare']->__toString(),
            'update query must same with compire operator'
        );

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE ( (test.ten = :ten) )',
            $update['equal']->__toString(),
            'update query must same with equal operator'
        );

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE (`test`.`column_1` IN (:in_0, :in_1, :in_2, :in_3, :in_4))',
            $update['in']->__toString(),
            'update query must same with in operator'
        );

        $this->assertEquals(
            'UPDATE `test` SET `a` = :bind_a WHERE col1 = :col1 OR col2 = :col2',
            $update['where']->__toString(),
            'update query must same with where operator'
        );
    }

    /** @test */
    public function itCorrectDeleteQuery(): void
    {
        $delete            = [];
        $delete['between'] = MyQuery::from('test', $this->PDO)
            ->delete()
            ->between('coulumn_1', 1, 100);

        $delete['compare'] = MyQuery::from('test', $this->PDO)
            ->delete()
            ->compare('ten', '>', 9);

        $delete['equal'] = MyQuery::from('test', $this->PDO)
            ->delete()
            ->equal('ten', 10);

        $delete['in'] = MyQuery::from('test', $this->PDO)
            ->delete()
            ->in('column_1', [1, 2, 3, 4, 5]);

        $delete['like'] = MyQuery::from('test', $this->PDO)
            ->delete()
            ->like('i', 'you');

        $delete['where'] = MyQuery::from('test', $this->PDO)
            ->delete()
            ->where('col1 = :col1 OR col2 = :col2', [['col1', 1], ['col2', 2]]);

        // assertation

        $this->assertEquals(
            'DELETE FROM `test` WHERE (`test`.`coulumn_1` BETWEEN :b_start AND :b_end)',
            $delete['between'],
            'delete query must same with between operator'
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (test.ten > :ten) )',
            $delete['compare'],
            'delete query must same with compire operator'
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE ( (test.ten = :ten) )',
            $delete['equal'],
            'delete query must same with equal operator'
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE (`test`.`column_1` IN (:in_0, :in_1, :in_2, :in_3, :in_4))',
            $delete['in'],
            'delete query must same with in operator'
        );

        $this->assertEquals(
            'DELETE FROM `test` WHERE col1 = :col1 OR col2 = :col2',
            $delete['where'],
            'delete query must same with where operator'
        );
    }

    /** @test */
    public function itCorrectSelectJoinQuery(): void
    {
        // inner join
        $select['inner'] = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'));

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN join_table ON base_table.base_id = join_table.join_id',
            $select['inner'],
            'expect select inner join'
        );

        // left join
        $select['left'] = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(LeftJoin::ref('join_table', 'base_id', 'join_id'));

        $this->assertEquals(
            'SELECT * FROM `base_table` LEFT JOIN join_table ON base_table.base_id = join_table.join_id',
            $select['left'],
            'expect select left join'
        );

        // right join
        $select['right'] = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(RightJoin::ref('join_table', 'base_id', 'join_id'));

        $this->assertEquals(
            'SELECT * FROM `base_table` RIGHT JOIN join_table ON base_table.base_id = join_table.join_id',
            $select['right'],
            'expect select right join'
        );

        // full join
        $select['full'] = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(FullJoin::ref('join_table', 'base_id', 'join_id'));

        $this->assertEquals(
            'SELECT * FROM `base_table` FULL OUTER JOIN join_table ON base_table.base_id = join_table.join_id',
            $select['full'],
            'expect select full join'
        );

        // cross join
        $select['cross'] = MyQuery::from('base_table', $this->PDO)
            ->select()
            ->join(CrossJoin::ref('join_table', 'base_table'));

        $this->assertEquals(
            'SELECT * FROM `base_table` CROSS JOIN join_table',
            $select['cross']
        );
    }

    /** @test */
    public function itCanGenerateWhereExisQuery(): void
    {
        $select = MyQuery::from('base_1', $this->PDO)
            ->select()
            ->whereExist(
                (new Select('base_2', ['*'], $this->PDO))
                // Select::from('base_2')
                ->equal('test', 'success')
                ->where('base_1.id = base_2.id')
            )
            ->limit(1, 10)
            ->order('id', MyQuery::ORDER_ASC)
            ->__toString();

        $this->assertEquals(
            'SELECT * FROM `base_1` WHERE EXISTS (SELECT * FROM `base_2` WHERE ( (base_2.test = :test) ) AND base_1.id = base_2.id) ORDER BY `base_1`.`id` ASC LIMIT 1, 10',
            $select,
            'where exist query'
        );

        // where not exist
        $select = MyQuery::from('base_1', $this->PDO)
            ->select()
            ->whereNotExist(
                (new Select('base_2', ['*'], $this->PDO))
                // Select::from('base_2', $this->PDO)
                ->equal('test', 'success')
                ->where('base_1.id = base_2.id')
            )
            ->limit(1, 10)
            ->order('id', MyQuery::ORDER_ASC)
            ->__toString();

        $this->assertEquals(
            'SELECT * FROM `base_1` WHERE NOT EXISTS (SELECT * FROM `base_2` WHERE ( (base_2.test = :test) ) AND base_1.id = base_2.id) ORDER BY `base_1`.`id` ASC LIMIT 1, 10',
            $select,
            'where exist query'
        );
    }
}
