<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Drop;

final class DropTest extends \QueryStringTest
{
    /** @test */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE test;',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExists()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF EXISTS test;',
            $schema->ifExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfExistsFalse()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExists()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF NOT EXISTS test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    /** @test */
    public function itCanGenerateCreateDatabaseIfNotExistsFalse()
    {
        $schema = new Drop('test', $this->pdo_schame);

        $this->assertEquals(
            'DROP TABLE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
