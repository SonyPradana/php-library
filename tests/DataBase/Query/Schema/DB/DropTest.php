<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\DB;

use System\Database\MySchema\DB\Drop;
use System\Test\Database\TestDatabaseQuery;

final class DropTest extends TestDatabaseQuery
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP DATABASE test;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExists()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP DATABASE IF EXISTS test;',
            $schema->ifExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExistsFalse()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP DATABASE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExists()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP DATABASE IF NOT EXISTS test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExistsFalse()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP DATABASE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
