<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Create;

final class CreateTest extends \QueryStringTest
{
    /** @test */
    public function itCanSetQuery()
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
}
