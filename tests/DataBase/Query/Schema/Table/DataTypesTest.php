<?php

declare(strict_types=1);

namespace System\Test\Database\Query\Schema\Table;

use System\Database\MySchema\Table\Create;
use System\Test\Database\TestDatabaseQuery;

final class DataTypesTest extends TestDatabaseQuery
{
    /** @test */
    public function itCanGenerateQueryUsingAddColumn()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schame);
        $schema('name')->varchar(40);
        $schema('size')->enum(['x-small', 'small', 'medium', 'large', 'x-large']);

        $this->assertEquals(
            "CREATE TABLE testing_db.test ( name varchar(40), size ENUM ('x-small', 'small', 'medium', 'large', 'x-large') )",
            $schema->__toString()
        );
    }
}
