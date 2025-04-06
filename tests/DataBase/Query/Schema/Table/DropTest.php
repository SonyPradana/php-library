<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Drop;
use System\Test\Database\TestDatabaseQuery;

final class DropTest extends TestDatabaseQuery
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE testing_db.test;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExists()
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF EXISTS testing_db.test;',
            $schema->ifExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExistsFalse()
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF NOT EXISTS testing_db.test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExists()
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF NOT EXISTS testing_db.test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExistsFalse()
    {
        $schema = new Drop('testing_db', 'test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF EXISTS testing_db.test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
