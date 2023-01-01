<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;
use System\Database\MyQuery\Join\CrossJoin;
use System\Database\MyQuery\Join\FullJoin;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Join\LeftJoin;
use System\Database\MyQuery\Join\RightJoin;

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
            'SELECT * FROM `base_table` INNER JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` INNER JOIN join_table ON base_table.base_id = join_table.join_id',
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
            'SELECT * FROM `base_table` LEFT JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` LEFT JOIN join_table ON base_table.base_id = join_table.join_id',
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
            'SELECT * FROM `base_table` RIGHT JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` RIGHT JOIN join_table ON base_table.base_id = join_table.join_id',
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
            'SELECT * FROM `base_table` FULL OUTER JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` FULL OUTER JOIN join_table ON base_table.base_id = join_table.join_id',
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
            'SELECT * FROM `base_table` CROSS JOIN join_table',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM `base_table` CROSS JOIN join_table',
            $join->queryBind()
        );
    }
}
