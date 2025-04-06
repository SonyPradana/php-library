<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\DB;

use System\Database\MySchema\DB\Create;
use System\Test\Database\TestDatabaseQuery;

final class CreateTest extends TestDatabaseQuery
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Create('test', $this->pdo_schame);

        $this->assertEquals(
            'CREATE DATABASE test;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExists()
    {
        $schema = new Create('test', $this->pdo_schame);

        $this->assertEquals(
            'CREATE DATABASE IF EXISTS test;',
            $schema->ifExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExistsFalse()
    {
        $schema = new Create('test', $this->pdo_schame);

        $this->assertEquals(
            'CREATE DATABASE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExists()
    {
        $schema = new Create('test', $this->pdo_schame);

        $this->assertEquals(
            'CREATE DATABASE IF NOT EXISTS test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExistsFalse()
    {
        $schema = new Create('test', $this->pdo_schame);

        $this->assertEquals(
            'CREATE DATABASE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
