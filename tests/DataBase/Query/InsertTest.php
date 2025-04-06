<?php

declare(strict_types=1);

namespace System\Test\Database\Query;

use System\Database\MyQuery;
use System\Test\Database\TestDatabaseQuery;

final class InsertTest extends TestDatabaseQuery
{
    /** @test */
    public function itCorrectInsert(): void
    {
        $insert = MyQuery::from('test', $this->pdo)
            ->insert()
            ->value('a', 1)
        ;

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (:bind_a)',
            $insert->__toString()
        );

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (1)',
            $insert->queryBind()
        );
    }

    /** @test */
    public function itCorrectInsertValues(): void
    {
        $insert = MyQuery::from('test', $this->pdo)
            ->insert()
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
        ;

        $this->assertEquals(
            'INSERT INTO test (a, c, e) VALUES (:bind_a, :bind_c, :bind_e)',
            $insert->__toString()
        );

        $this->assertEquals(
            "INSERT INTO test (a, c, e) VALUES ('b', 'd', 'f')",
            $insert->queryBind()
        );
    }

    /** @test */
    public function itCorrectInsertQueryMultyValues(): void
    {
        $insert = MyQuery::from('test', $this->pdo)
            ->insert()
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
            ->value('g', 'h')
        ;

        $this->assertEquals(
            'INSERT INTO test (a, c, e, g) VALUES (:bind_a, :bind_c, :bind_e, :bind_g)',
            $insert->__toString()
        );

        $this->assertEquals(
            "INSERT INTO test (a, c, e, g) VALUES ('b', 'd', 'f', 'h')",
            $insert->queryBind()
        );
    }

    /** @test */
    public function itCorrectInsertQueryMultyRaws(): void
    {
        $insert = MyQuery::from('test', $this->pdo)
            ->insert()
            ->rows([
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
            'INSERT INTO test (a, c, e) VALUES (:bind_0_a, :bind_0_c, :bind_0_e), (:bind_1_a, :bind_1_c, :bind_1_e)',
            $insert->__toString()
        );

        $this->assertEquals(
            "INSERT INTO test (a, c, e) VALUES ('b', 'd', 'f'), ('b', 'd', 'f')",
            $insert->queryBind()
        );
    }

    /** @test */
    public function itCorrectInsertOnDuplicateKeyUpdate(): void
    {
        $insert = MyQuery::from('test', $this->pdo)
            ->insert()
            ->value('a', 1)
            ->on('a')
        ;

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (:bind_a) ON DUPLICATE KEY UPDATE a = VALUES(a)',
            $insert->__toString()
        );

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (1) ON DUPLICATE KEY UPDATE a = VALUES(a)',
            $insert->queryBind()
        );
    }
}
