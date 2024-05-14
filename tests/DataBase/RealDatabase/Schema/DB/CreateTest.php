<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase\Schema\DB;

use System\Database\MySchema\DB\Create;

final class CreateTest extends \TestDatabaseConnection
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanGenerateCreateDatabase()
    {
        // need clean up
        $this->tearDown();
        $schema = new Create($this->pdo_schema->configs()['database_name'], $this->pdo_schema);

        $this->assertTrue($schema->execute());
    }
}
