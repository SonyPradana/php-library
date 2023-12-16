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
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingWithMultyPrimeryKey()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->primaryKey('LastName');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`, `LastName`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAddColumnWithoutPrimeryKey()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAddColumnWithUnique()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->unique('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), UNIQUE (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingAddColumnWithMultyUnique()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->unique('PersonID');
        $schema->unique('LastName');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), UNIQUE (`PersonID`, `LastName`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryUsingColumns()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->collumns([
                (new Column())->raw('PersonID int'),
                (new Column())->raw('LastName varchar(255)'),
        ]);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQuery()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema('PersonID')->int();
        $schema('LastName')->varchar(255);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( `PersonID` int, `LastName` varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryWithDatatypeAndConstrait()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema('PersonID')->int()->notNull();
        $schema('LastName')->varchar(255);
        $schema->primaryKey('PersonID');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( `PersonID` int NOT NULL, `LastName` varchar(255), PRIMARY KEY (`PersonID`) )',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryWithStorageEngine()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->engine(Create::INNODB);

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) ) ENGINE=INNODB',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryWithCharacterSet()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->character('utf8mb4');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) ) CHARACTER SET utf8mb4',
            $schema->__toString()
        );
    }

    /** @test */
    public function itCanGenerateQueryWithEngineStoreAndCharacterSet()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema->addColumn()->raw('PersonID int');
        $schema->addColumn()->raw('LastName varchar(255)');
        $schema->primaryKey('PersonID');
        $schema->engine(Create::INNODB);
        $schema->character('utf8mb4');

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) ) ENGINE=INNODB CHARACTER SET utf8mb4',
            $schema->__toString()
        );
    }
}
