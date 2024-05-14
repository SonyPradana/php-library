<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\Table;

use System\Database\MySchema\Table\Raw;

final class RawTest extends \TestDatabaseConnection
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanGenerateCreateDatabase()
    {
        $schema = new Raw('CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (`PersonID`) )', $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
