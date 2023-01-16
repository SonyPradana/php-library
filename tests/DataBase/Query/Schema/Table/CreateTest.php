<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Column;
use System\Database\MySchema\Table\Create;

final class CreateTest extends \QueryStringTest
{
    /** @test */
    public function itCanGenerateQueryUsingAddColumn()
    {
        $schema = new Create('test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAddColumnWithoutPrimeryKey()
    {
        $schema = new Create('test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');

        $this->assertEquals(
            'CREATE TABLE test ( PersonID int, LastName varchar(255) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingColumns()
    {
        $schema = new Create('test', $this->pdo_schame);
        $schema->collumns([
                (new Column())->raw('PersonID int'),
                (new Column())->raw('LastName varchar(255)'),
        ]);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQuery()
    {
        $schema = new Create('test', $this->pdo_schame);
        $schema('PersonID')->int();
        $schema('LastName')->varchar(255);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }
}
