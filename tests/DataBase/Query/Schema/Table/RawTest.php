<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Raw;

final class RawTest extends \QueryStringTest
{
    /** @test */
    public function itCanGenerateQueryUsingAddColumn()
    {
        $schema = new Raw('CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )', $this->pdo_schame);

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }
}
